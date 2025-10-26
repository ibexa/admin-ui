<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\UI\Value\Content\RelationInterface;
use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\RelationListItem;
use Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\UnauthorizedRelationListItem;
use Ibexa\Contracts\Core\Repository\Values\Content\RelationList\RelationListItemInterface;

final class RelationListDataset
{
    /** @var ContentService */
    private $contentService;

    /** @var ValueFactory */
    private $valueFactory;

    /** @var RelationInterface[] */
    private $relations;

    /**
     * @param ContentService $contentService
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        ContentService $contentService,
        ValueFactory $valueFactory
    ) {
        $this->contentService = $contentService;
        $this->valueFactory = $valueFactory;
        $this->relations = [];
    }

    /**
     * @param Content $content
     *
     * @return RelationListDataset
     *
     * @throws UnauthorizedException
     */
    public function load(
        Content $content,
        int $offset = 0,
        int $limit = 10
    ): self {
        $versionInfo = $content->getVersionInfo();
        $relationListItems = $this->contentService->loadRelationList($versionInfo, $offset, $limit)->items;

        $this->relations = array_map(
            function (RelationListItemInterface $relationListItem) use ($content) {
                if ($relationListItem->hasRelation()) {
                    /** @var RelationListItem $relationListItem */
                    return $this->valueFactory->createRelationItem(
                        $relationListItem,
                        $content
                    );
                }

                /** @var UnauthorizedRelationListItem $relationListItem */
                return $this->valueFactory->createUnauthorizedRelationItem(
                    $relationListItem
                );
            },
            $relationListItems
        );

        return $this;
    }

    /**
     * @return RelationInterface[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }
}

class_alias(RelationListDataset::class, 'EzSystems\EzPlatformAdminUi\UI\Dataset\RelationListDataset');
