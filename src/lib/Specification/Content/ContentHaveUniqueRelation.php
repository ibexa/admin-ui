<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Content;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Pagination\Adapter\RelationListIteratorAdapter;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Iterator\BatchIterator;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\RelationType;
use Ibexa\Contracts\Core\Specification\AbstractSpecification;

class ContentHaveUniqueRelation extends AbstractSpecification
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     */
    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * @param $item
     *
     * @return bool
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function isSatisfiedBy($item): bool
    {
        if (!$item instanceof Content) {
            throw new InvalidArgumentException($item, sprintf('Must be an instance of %s', Content::class));
        }

        $relationListIterator = new BatchIterator(
            new RelationListIteratorAdapter(
                $this->contentService,
                $item->getVersionInfo(),
                RelationType::ASSET
            )
        );

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\RelationListItem $relationItem */
        foreach ($relationListIterator as $relationItem) {
            if ($relationItem->hasRelation()) {
                /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Relation $relation */
                $relation = $relationItem->getRelation();
                $relationsFromAssetSide = $this->contentService->countReverseRelations(
                    $relation->getDestinationContentInfo()
                );

                if ($relationsFromAssetSide > 1) {
                    return false;
                }
            }
        }

        return true;
    }
}
