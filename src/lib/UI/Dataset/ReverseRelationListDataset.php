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
use Ibexa\Contracts\Core\Repository\Values\Content\RelationList\RelationListItemInterface;

final class ReverseRelationListDataset
{
    /** @var \Ibexa\AdminUi\UI\Value\Content\RelationInterface[] */
    private array $reverseRelations;

    public function __construct(
        private readonly ContentService $contentService,
        private readonly ValueFactory $valueFactory
    ) {
        $this->reverseRelations = [];
    }

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
            function (RelationListItemInterface $relationListItem) use ($content): RelationInterface {
                if ($relationListItem->hasRelation()) {
                    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\RelationListItem $relationListItem */
                    return $this->valueFactory->createRelationItem(
                        $relationListItem,
                        $content
                    );
                }

                /** @var \Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\UnauthorizedRelationListItem $relationListItem */
                return $this->valueFactory->createUnauthorizedRelationItem(
                    $relationListItem
                );
            },
            $reverseRelationListItems
        );

        return $this;
    }

    /**
     * @return \Ibexa\AdminUi\UI\Value\Content\RelationInterface[]
     */
    public function getReverseRelations(): array
    {
        return $this->reverseRelations;
    }
}
