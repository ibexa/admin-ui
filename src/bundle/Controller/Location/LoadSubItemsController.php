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
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationList;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ParentLocationId;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Core\Repository\Values\Filter\Filter;
use Ibexa\Contracts\Core\Repository\Values\Filter\FilteringSortClause;
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

    public function __construct(readonly private LocationService $locationService)
    {
    }

    public function loadAction(
        Request $request,
        Location $location,
        int $limit,
        int $offset
    ): SubItemList {
        $sortOrder = $request->query->getAlpha('sortOrder', Query::SORT_ASC);

        $filter = new Filter(new ParentLocationId($location->getId()));
        $filter->withLimit($limit);
        $filter->withOffset($offset);

        $sortClauses = $request->query->get('sortClause') ? [$this->buildSortClause($request->query->get('sortClause'), $sortOrder)] : $this->getDefaultSortClause($location);

        foreach ($sortClauses as $sortClause) {
            $filter->withSortClause($sortClause);
        }

        $count = $this->locationService->count($filter);
        $children = $this->locationService->find($filter);

        return $this->buildSubItemsList(
            $count,
            $children
        );
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Filter\FilteringSortClause[]
     */
    private function getDefaultSortClause(Location $location): array
    {
        try {
            $sortClauses = $location->getSortClauses();
        } catch (NotImplementedException $e) {
            return [];
        }

        return array_filter($sortClauses, static fn ($sortClause) => $sortClause instanceof FilteringSortClause);
    }

    private function buildSortClause(string $sortClause, string $sortOrder): FilteringSortClause
    {
        if (!isset(static::SORT_CLAUSE_MAP[$sortClause])) {
            throw new InvalidArgumentException('$sortClause', 'Invalid sort clause');
        }

        $map = static::SORT_CLAUSE_MAP;

        $sortClauseInstance = new $map[$sortClause]();
        $sortClauseInstance->direction = $sortOrder;

        return $sortClauseInstance;
    }

    private function buildSubItemsList(int $totalCount, LocationList $childrenList): SubItemList
    {
        $subItems = [];
        foreach ($childrenList as $child) {
            $content = $child->getContent();
            $contentInfo = $child->getContentInfo();
            $versionInfo = $content->getVersionInfo();
            $owner = $child->getContentInfo()->getOwner();
            $sectionName = null;
            try {
                $sectionName = $contentInfo->getSection()->name;
            } catch (UnauthorizedException $e) {
            }
            $subItems[] = new SubItem(
                $child->getId(),
                $child->remoteId,
                $child->isHidden(),
                $child->isInvisible(),
                $child->priority,
                $child->getPathString(),
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
