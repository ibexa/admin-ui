<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Trash;

use Ibexa\Contracts\Core\Repository\Values\Content\TrashItem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo add validation
 */
final class TrashItemDeleteData
{
    /**
     * @param TrashItem[] $trashItems
     */
    public function __construct(
        #[Assert\NotBlank]
        private array $trashItems = []
    ) {}

    /**
     * @return TrashItem[]
     */
    public function getTrashItems(): array
    {
        return $this->trashItems;
    }

    /**
     * @param TrashItem[] $trashItems
     */
    public function setTrashItems(array $trashItems): void
    {
        $this->trashItems = $trashItems;
    }
}
