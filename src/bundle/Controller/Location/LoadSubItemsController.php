<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\Location;

use Ibexa\AdminUi\REST\Value\SubItems\ContentInfo;
use Ibexa\AdminUi\REST\Value\SubItems\ContentType;
use Ibexa\AdminUi\REST\Value\SubItems\Owner;
use Ibexa\AdminUi\REST\Value\SubItems\SubItem;
use Ibexa\AdminUi\REST\Value\SubItems\SubItemList;
use Ibexa\AdminUi\REST\Value\SubItems\Thumbnail;
use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ParentLocationId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Rest\Server\Controller as RestController;
use Symfony\Component\HttpFoundation\Request;

final class LoadSubItemsController extends RestController
{
    private const array SORT_CLAUSE_MAP = [
        'ContentId' => SortClause\ContentId::class,
        'DateModified' => SortClause\DateModified::class,
        'LocationDepth' => SortClause\Location\Depth::class,
        'LocationPath' => SortClause\Location\Path::class,
        'LocationPriority' => SortClause\Location\Priority::class,
        'SectionIdentifier' => SortClause\SectionIdentifier::class,
        'SectionName' => SortClause\SectionName::class,
        'DatePublished' => SortClause\DatePublished::class,
        'ContentName' => SortClause\ContentName::class,
    ];

    public function __construct(readonly private SearchService $searchService)
    {
    }

    public function loadAction(
        Request $request,
        Location $location,
        int $limit,
        int $offset
    ): SubItemList {
        $sortOrder = $request->query->getAlpha('sortOrder', Query::SORT_ASC);

        $sortClauses = $request->query->get('sortClause')
            ? [$this->buildSortClause($request->query->get('sortClause'), $sortOrder)]
            : $this->getDefaultSortClause($location);

        $query = new LocationQuery();
        $query->filter = new ParentLocationId($location->getId());
        $query->limit = $limit;
        $query->offset = $offset;
        $query->sortClauses = $sortClauses;

        $searchResult = $this->searchService->findLocations($query);

        return $this->buildSubItemsList(
            $searchResult->totalCount ?? 0,
            $searchResult->searchHits
        );
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause[]
     */
    private function getDefaultSortClause(Location $location): array
    {
        try {
            $sortClauses = $location->getSortClauses();
        } catch (NotImplementedException $e) {
            return [];
        }

        return $sortClauses;
    }

    private function buildSortClause(string $sortClause, string $sortOrder): SortClause
    {
        if (!isset(static::SORT_CLAUSE_MAP[$sortClause])) {
            throw new InvalidArgumentException('$sortClause', 'Invalid sort clause');
        }

        $map = static::SORT_CLAUSE_MAP;

        $sortClauseInstance = new $map[$sortClause]();
        $sortClauseInstance->direction = $sortOrder;

        return $sortClauseInstance;
    }

    /**
     * @param array<\Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchHit<\Ibexa\Contracts\Core\Repository\Values\Content\Location>> $childrenList
     */
    private function buildSubItemsList(int $totalCount, array $childrenList): SubItemList
    {
        $subItems = [];
        foreach ($childrenList as $hit) {
            $location = $hit->valueObject;
            $content = $location->getContent();
            $contentInfo = $location->getContentInfo();
            $versionInfo = $content->getVersionInfo();
            $owner = $location->getContentInfo()->getOwner();
            try {
                $sectionName = $contentInfo->getSection()->name;
            } catch (UnauthorizedException $e) {
                $sectionName = null;
            }
            $subItems[] = new SubItem(
                $location->getId(),
                $location->remoteId,
                $location->isHidden(),
                $location->isInvisible(),
                $location->priority,
                $location->getPathString(),
                new Thumbnail(
                    $content->getThumbnail()?->resource,
                    $content->getThumbnail()?->mimeType
                ),
                new Owner(
                    $owner->getId(),
                    new Thumbnail(
                        $owner->getThumbnail()?->resource,
                        $owner->getThumbnail()?->mimeType
                    ),
                    new ContentType(
                        $owner->getContentType()->getIdentifier(),
                        $owner->getContentType()->getName(),
                    ),
                    $owner->getName(),
                ),
                $versionInfo->getVersionNo(),
                $versionInfo->getLanguageCodes(),
                new Owner(
                    $versionInfo->getCreator()->getId(),
                    new Thumbnail(
                        $versionInfo->getCreator()->getThumbnail()?->resource,
                        $versionInfo->getCreator()->getThumbnail()?->mimeType
                    ),
                    new ContentType(
                        $versionInfo->getCreator()->getContentType()->getIdentifier(),
                        $versionInfo->getCreator()->getContentType()->getName(),
                    ),
                    $versionInfo->getCreator()->getName(),
                ),
                new ContentType(
                    $content->getContentType()->getIdentifier(),
                    $content->getContentType()->getName(),
                ),
                new ContentInfo(
                    $contentInfo->getId(),
                    $contentInfo->remoteId,
                    $contentInfo->getMainLanguageCode(),
                    $contentInfo->publishedDate->getTimestamp(),
                    $contentInfo->modificationDate->getTimestamp(),
                    $sectionName,
                    $content->getName()
                ),
            );
        }

        return new SubItemList($totalCount, $subItems);
    }
}
