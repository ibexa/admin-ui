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
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\RelationListItem;
use Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\UnauthorizedRelationListItem;
use Ibexa\Contracts\Core\Repository\Values\Content\RelationList\RelationListItemInterface;

final class ReverseRelationListDataset
{
    /** @var ContentService */
    private $contentService;

    /** @var ValueFactory */
    private $valueFactory;

    /** @var RelationInterface[] */
    private $reverseRelations;

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
        $this->reverseRelations = [];
    }

    /**
     * @param Content $content
     * @param int $offset
     * @param int $limit
     *
     * @return ReverseRelationListDataset
     */
    public function load(
        Content $content,
        int $offset = 0,
        int $limit = 10
    ): self {
        $versionInfo = $content->getVersionInfo();

        $reverseRelationListItems = $this->contentService->loadReverseRelationList(
            $versionInfo->getContentInfo(),
            $offset,
            $limit
        )->items;

        $this->reverseRelations = array_map(
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
            $reverseRelationListItems
        );

        return $this;
    }

    /**
     * @return RelationInterface[]
     */
    public function getReverseRelations(): array
    {
        return $this->reverseRelations;
    }
}

class_alias(ReverseRelationListDataset::class, 'EzSystems\EzPlatformAdminUi\UI\Dataset\ReverseRelationListDataset');
