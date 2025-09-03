<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Content;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Iterator\BatchIterator;
use Ibexa\Contracts\Core\Repository\Iterator\BatchIteratorAdapter\RelationListIteratorAdapter;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\RelationType;
use Ibexa\Contracts\Core\Specification\AbstractSpecification;

final class ContentHaveUniqueRelation extends AbstractSpecification
{
    public function __construct(private readonly ContentService $contentService)
    {
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function isSatisfiedBy(mixed $item): bool
    {
        if (!$item instanceof Content) {
            throw new InvalidArgumentException(
                $item,
                sprintf('Must be an instance of %s', Content::class)
            );
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
