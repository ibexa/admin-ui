<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\UI\Value as UIValue;
use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Iterator\BatchIterator;
use Ibexa\Contracts\Core\Repository\Iterator\BatchIteratorAdapter\RelationListIteratorAdapter;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\RelationListItem;

class RelationsDataset
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    protected $contentService;

    /** @var \Ibexa\AdminUi\UI\Value\ValueFactory */
    protected $valueFactory;

    /** @var UIValue\Content\Relation[] */
    protected $relations;

    /** @var UIValue\Content\Relation[] */
    protected $reverseRelations;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\AdminUi\UI\Value\ValueFactory $valueFactory
     */
    public function __construct(ContentService $contentService, ValueFactory $valueFactory)
    {
        $this->contentService = $contentService;
        $this->valueFactory = $valueFactory;
        $this->relations = [];
        $this->reverseRelations = [];
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function load(Content $content): self
    {
        $versionInfo = $content->getVersionInfo();

        $relationListIterator = new BatchIterator(
            new RelationListIteratorAdapter(
                $this->contentService,
                $versionInfo
            )
        );

        foreach ($relationListIterator as $relationItem) {
            if ($relationItem->hasRelation()) {
                /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Relation $relation */
                $relation = $relationItem->getRelation();
                $this->relations[] = $this->valueFactory->createRelationItem(
                    new RelationListItem($relation),
                    $content
                );
            }
        }

        foreach ($this->contentService->loadReverseRelations($versionInfo->getContentInfo()) as $reverseRelation) {
            $this->reverseRelations[] = $this->valueFactory->createRelationItem(
                new RelationListItem($reverseRelation),
                $content
            );
        }

        return $this;
    }

    /**
     * @return UIValue\Content\Relation[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @return UIValue\Content\Relation[]
     */
    public function getReverseRelations(): array
    {
        return $this->reverseRelations;
    }
}
