<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\Location;

use Ibexa\AdminUi\REST\Value\SubItems\ContentType;
use Ibexa\AdminUi\REST\Value\SubItems\Owner;
use Ibexa\AdminUi\REST\Value\SubItems\SubItem;
use Ibexa\AdminUi\REST\Value\SubItems\SubItemList;
use Ibexa\AdminUi\REST\Value\SubItems\Thumbnail;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationList;
use Ibexa\Rest\Server\Controller as RestController;

final class LoadSubItemsController extends RestController
{
    public function __construct(readonly private LocationService $locationService)
    {
    }

    public function loadAction(
        Location $location,
        int $limit,
        int $offset
    ): SubItemList {
        $count = $this->locationService->getLocationChildCount($location);

        $children = $this->locationService->loadLocationChildren($location, $offset, $limit);

        return $this->buildSubItemsList(
            $count,
            $children
        );
    }

    private function buildSubItemsList(int $totalCount, LocationList $childrenList): SubItemList
    {
        $subItems = [];
        foreach ($childrenList as $child) {
            $content = $child->getContent();
            $versionInfo = $content->getVersionInfo();
            $owner = $child->getContentInfo()->getOwner();
            $subItems[] = new SubItem(
                $child->getId(),
                $child->remoteId,
                $child->isHidden(),
                $child->priority,
                $child->getPathString(),
                new Thumbnail(
                    $content->getThumbnail()?->resource,
                    $content->getThumbnail()?->mimeType
                ),
                $content->getContentInfo()->remoteId,
                $content->getContentInfo()->getMainLanguageCode(),
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
                $child->getContentInfo()->getSection()->name,
                $child->getContentInfo()->publishedDate->getTimestamp(),
                $child->getContentInfo()->modificationDate->getTimestamp(),
                $content->getName(),
            );
        }

        return new SubItemList($totalCount, $subItems);
    }
}
