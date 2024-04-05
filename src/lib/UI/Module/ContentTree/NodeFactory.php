<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Module\ContentTree;

use Ibexa\AdminUi\REST\Value\ContentTree\LoadSubtreeRequestNode;
use Ibexa\AdminUi\REST\Value\ContentTree\Node;
use Ibexa\AdminUi\Siteaccess\SiteaccessResolverInterface;
use Ibexa\Contracts\Core\Repository\BookmarkService;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\TermAggregationResult;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Helper\TranslationHelper;
use Ibexa\Core\Repository\Repository;

/**
 * @internal
 */
final class NodeFactory
{
    private const TOP_NODE_CONTENT_ID = 0;

    /**
     * @var array<string, class-string<\Ibexa\Contracts\Core\Repository\Values\Filter\FilteringSortClause>>
     */
    private const SORT_CLAUSE_MAP = [
        'DatePublished' => SortClause\DatePublished::class,
        'ContentName' => SortClause\ContentName::class,
    ];

    private BookmarkService $bookmarkService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /** @var \Ibexa\Contracts\Core\Repository\SearchService */
    private $searchService;

    /** @var \Ibexa\Core\Helper\TranslationHelper */
    private $translationHelper;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    private PermissionResolver $permissionResolver;

    private Repository $repository;

    private SiteaccessResolverInterface  $siteaccessResolver;

    /** @var int */
    private $maxLocationIdsInSingleAggregation;

    public function __construct(
        BookmarkService $bookmarkService,
        ContentService $contentService,
        SearchService $searchService,
        TranslationHelper $translationHelper,
        ConfigResolverInterface $configResolver,
        PermissionResolver $permissionResolver,
        Repository $repository,
        SiteaccessResolverInterface $siteaccessResolver,
        int $maxLocationIdsInSingleAggregation
    ) {
        $this->bookmarkService = $bookmarkService;
        $this->contentService = $contentService;
        $this->searchService = $searchService;
        $this->translationHelper = $translationHelper;
        $this->configResolver = $configResolver;
        $this->permissionResolver = $permissionResolver;
        $this->repository = $repository;
        $this->siteaccessResolver = $siteaccessResolver;
        $this->maxLocationIdsInSingleAggregation = $maxLocationIdsInSingleAggregation;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function createNode(
        Location $location,
        ?LoadSubtreeRequestNode $loadSubtreeRequestNode = null,
        bool $loadChildren = false,
        int $depth = 0,
        ?string $sortClause = null,
        string $sortOrder = Query::SORT_ASC,
        ?Criterion $requestFilter = null
    ): Node {
        $uninitializedContentInfoList = [];
        $containerLocations = [];

        $userBookmarks = $this->bookmarkService->loadBookmarks(0, -1);
        $bookmarkedLocations = array_flip(array_column($userBookmarks->items, 'id'));

        $node = $this->buildNode(
            $location,
            $uninitializedContentInfoList,
            $containerLocations,
            $loadSubtreeRequestNode,
            $loadChildren,
            $depth,
            $sortClause,
            $sortOrder,
            $bookmarkedLocations,
            $requestFilter
        );
        $versionInfoById = $this->contentService->loadVersionInfoListByContentInfo($uninitializedContentInfoList);

        $aggregatedChildrenCount = null;
        if ($this->searchService->supports(SearchService::CAPABILITY_AGGREGATIONS)) {
            $aggregatedChildrenCount = $this->countAggregatedSubitems($containerLocations, $requestFilter);
        }

        $this->supplyTranslatedContentName($node, $versionInfoById);
        $this->supplyChildrenCount($node, $aggregatedChildrenCount, $requestFilter);

        return $node;
    }

    private function resolveLoadLimit(?LoadSubtreeRequestNode $loadSubtreeRequestNode): int
    {
        $limit = $this->getSetting('load_more_limit');

        if (null !== $loadSubtreeRequestNode) {
            $limit = $loadSubtreeRequestNode->limit;
        }

        if ($limit > $this->getSetting('children_load_max_limit')) {
            $limit = $this->getSetting('children_load_max_limit');
        }

        return $limit;
    }

    private function findSubitems(
        Location $parentLocation,
        int $limit = 10,
        int $offset = 0,
        ?string $sortClause = null,
        string $sortOrder = Query::SORT_ASC,
        ?Criterion $requestFilter = null
    ): SearchResult {
        $searchQuery = $this->getSearchQuery($parentLocation->id, $requestFilter);

        $searchQuery->limit = $limit;
        $searchQuery->offset = $offset;
        $searchQuery->sortClauses = $this->getSortClauses($sortClause, $sortOrder, $parentLocation);

        return $this->searchService->findLocations($searchQuery);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $parentLocation
     */
    private function getSearchQuery(int $parentLocationId, ?Criterion $requestFilter = null): LocationQuery
    {
        $searchQuery = new LocationQuery();
        $searchQuery->filter = new Criterion\ParentLocationId($parentLocationId);

        $contentTypeCriterion = null;

        if (!empty($this->getSetting('allowed_content_types'))) {
            $contentTypeCriterion = new Criterion\ContentTypeIdentifier($this->getSetting('allowed_content_types'));
        }

        if (empty($this->allowedContentTypes) && !empty($this->getSetting('ignored_content_types'))) {
            $contentTypeCriterion = new Criterion\LogicalNot(
                new Criterion\ContentTypeIdentifier($this->getSetting('ignored_content_types'))
            );
        }

        if (null !== $contentTypeCriterion) {
            $searchQuery->filter = new Criterion\LogicalAnd([$searchQuery->filter, $contentTypeCriterion]);
        }

        if (null !== $requestFilter) {
            $searchQuery->filter = new Criterion\LogicalAnd([$searchQuery->filter, $requestFilter]);
        }

        return $searchQuery;
    }

    private function findChild(int $locationId, LoadSubtreeRequestNode $loadSubtreeRequestNode): ?LoadSubtreeRequestNode
    {
        foreach ($loadSubtreeRequestNode->children as $child) {
            if ($child->locationId === $locationId) {
                return $child;
            }
        }

        return null;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function countSubitems(int $parentLocationId, ?Criterion $requestFilter = null): int
    {
        $searchQuery = $this->getSearchQuery($parentLocationId, $requestFilter);

        $searchQuery->limit = 0;
        $searchQuery->offset = 0;
        $searchQuery->performCount = true;

        return $this->searchService->findLocations($searchQuery)->totalCount;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[] $containerLocations
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidCriterionArgumentException
     */
    private function countAggregatedSubitems(array $containerLocations, ?Criterion $requestFilter): array
    {
        if (empty($containerLocations)) {
            return [];
        }

        if (\count($containerLocations) > $this->maxLocationIdsInSingleAggregation) {
            $containerLocationsChunks = array_chunk($containerLocations, $this->maxLocationIdsInSingleAggregation);

            $result = [];
            foreach ($containerLocationsChunks as $containerLocationsChunk) {
                $result = array_replace($result, $this->countAggregatedSubitems($containerLocationsChunk, $requestFilter));
            }

            return $result;
        }

        $parentLocationIds = array_column($containerLocations, 'id');

        $searchQuery = new LocationQuery();
        $searchQuery->filter = new Criterion\ParentLocationId($parentLocationIds);
        $locationChildrenTermAggregation = new Query\Aggregation\Location\LocationChildrenTermAggregation('childrens');
        $locationChildrenTermAggregation->setLimit(\count($parentLocationIds));
        $searchQuery->aggregations[] = $locationChildrenTermAggregation;

        if (null !== $requestFilter) {
            $searchQuery->filter = new Criterion\LogicalAnd([$searchQuery->filter, $requestFilter]);
        }

        $result = $this->searchService->findLocations($searchQuery);

        if ($result->aggregations->has('childrens')) {
            return $this->aggregationResultToArray($result->aggregations->get('childrens'));
        }

        return [];
    }

    /**
     * @return array<int,int>
     */
    private function aggregationResultToArray(TermAggregationResult $aggregationResult): array
    {
        $resultsAsArray = [];
        foreach ($aggregationResult->getEntries() as $entry) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
            $location = $entry->getKey();
            $resultsAsArray[$location->id] = $entry->getCount();
        }

        return $resultsAsArray;
    }

    /**
     * @return mixed
     */
    private function getSetting(string $name)
    {
        return $this->configResolver->getParameter("content_tree_module.$name");
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function buildSortClause(string $sortClause, string $sortOrder): SortClause
    {
        if (!isset(static::SORT_CLAUSE_MAP[$sortClause])) {
            throw new InvalidArgumentException('$sortClause', 'Invalid sort clause');
        }

        $map = static::SORT_CLAUSE_MAP;

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause $sortClauseInstance */
        $sortClauseInstance = new $map[$sortClause]();
        $sortClauseInstance->direction = $sortOrder;

        return $sortClauseInstance;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause[]
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function getSortClauses(
        ?string $sortClause,
        string $sortOrder,
        Location $parentLocation
    ): array {
        if ($sortClause) {
            return [$this->buildSortClause($sortClause, $sortOrder)];
        }

        try {
            return $parentLocation->getSortClauses();
        } catch (NotImplementedException $e) {
            return []; // rely on storage engine default sorting
        }
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo[] $uninitializedContentInfoList
     * @param array<int, int> $bookmarkLocations
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function buildNode(
        Location $location,
        array &$uninitializedContentInfoList,
        array &$containerLocations,
        ?LoadSubtreeRequestNode $loadSubtreeRequestNode = null,
        bool $loadChildren = false,
        int $depth = 0,
        ?string $sortClause = null,
        string $sortOrder = Query::SORT_ASC,
        array $bookmarkLocations = [],
        ?Criterion $requestFilter = null
    ): Node {
        $contentInfo = $location->getContentInfo();
        $contentId = $location->contentId;
        if (!isset($uninitializedContentInfoList[$contentId])) {
            $uninitializedContentInfoList[$contentId] = $contentInfo;
        }

        // Top Level Location (id = 1) does not have a content type
        $contentType = $location->depth > 0
            ? $contentInfo->getContentType()
            : null;

        if ($contentType !== null && $contentType->isContainer) {
            $containerLocations[] = $location;
        }

        $content = $location->getContent();
        $versionInfo = $content->getVersionInfo();

        $limit = $this->resolveLoadLimit($loadSubtreeRequestNode);
        $offset = null !== $loadSubtreeRequestNode
            ? $loadSubtreeRequestNode->offset
            : 0;

        $totalChildrenCount = 0;
        $children = [];
        if ($loadChildren && $depth < $this->getSetting('tree_max_depth')) {
            $searchResult = $this->findSubitems($location, $limit, $offset, $sortClause, $sortOrder, $requestFilter);
            $totalChildrenCount = (int) $searchResult->totalCount;

            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $childLocation */
            foreach (array_column($searchResult->searchHits, 'valueObject') as $childLocation) {
                $childLoadSubtreeRequestNode = null !== $loadSubtreeRequestNode
                    ? $this->findChild($childLocation->id, $loadSubtreeRequestNode)
                    : null;

                $children[] = $this->buildNode(
                    $childLocation,
                    $uninitializedContentInfoList,
                    $containerLocations,
                    $childLoadSubtreeRequestNode,
                    null !== $childLoadSubtreeRequestNode,
                    $depth + 1,
                    null,
                    Query::SORT_ASC,
                    $bookmarkLocations,
                    $requestFilter
                );
            }
        }

        $translations = $versionInfo->languageCodes;
        $previewableTranslations = array_filter(
            $translations,
            fn (string $languageCode): bool => $this->isPreviewable($location, $content, $languageCode)
        );

        return new Node(
            $depth,
            $location->id,
            $location->contentId,
            $versionInfo->versionNo,
            $translations,
            $previewableTranslations,
            '', // node name will be provided later by `supplyTranslatedContentName` method
            $contentType ? $contentType->identifier : '',
            $contentType ? $contentType->isContainer : true,
            $location->invisible || $location->hidden,
            $limit,
            $totalChildrenCount,
            $this->getReverseRelationsCount($contentInfo),
            isset($bookmarkLocations[$location->id]),
            $children,
            $location->getPathString()
        );
    }

    private function getReverseRelationsCount(ContentInfo $contentInfo): int
    {
        return $this->permissionResolver->sudo(
            static function (Repository $repository) use ($contentInfo): int {
                return $repository->getContentService()->countReverseRelations($contentInfo);
            },
            $this->repository
        );
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo[] $versionInfoById
     */
    private function supplyTranslatedContentName(Node $node, array $versionInfoById): void
    {
        if ($node->contentId !== self::TOP_NODE_CONTENT_ID) {
            $node->name = $this->translationHelper->getTranslatedContentNameByVersionInfo($versionInfoById[$node->contentId]);
        }

        foreach ($node->children as $child) {
            $this->supplyTranslatedContentName($child, $versionInfoById);
        }
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function supplyChildrenCount(
        Node $node,
        ?array $aggregationResult = null,
        ?Criterion $requestFilter = null
    ): void {
        if ($node->isContainer) {
            if ($aggregationResult !== null) {
                $totalCount = $aggregationResult[$node->locationId] ?? 0;
            } else {
                $totalCount = $this->countSubitems($node->locationId, $requestFilter);
            }

            $node->totalChildrenCount = $totalCount;
        }

        foreach ($node->children as $child) {
            $this->supplyChildrenCount($child, $aggregationResult, $requestFilter);
        }
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function isPreviewable(
        Location $location,
        Content $content,
        string $languageCode
    ): bool {
        $versionNo = $content->getVersionInfo()->versionNo;

        $siteAccesses = $this->siteaccessResolver->getSiteAccessesListForLocation(
            $location,
            $versionNo,
            $languageCode
        );

        $canPreview = $this->permissionResolver->canUser(
            'content',
            'versionread',
            $content,
            [$location]
        );

        return $canPreview && !empty($siteAccesses);
    }
}

class_alias(NodeFactory::class, 'EzSystems\EzPlatformAdminUi\UI\Module\ContentTree\NodeFactory');
