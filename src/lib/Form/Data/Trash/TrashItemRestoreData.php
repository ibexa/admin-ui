<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Trash;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\TrashItem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo add validation
 */
final class TrashItemRestoreData
{
    /**
     * @param TrashItem[] $trashItems
     */
    public function __construct(
        /**
         * @var TrashItem[]
         */
        #[Assert\NotBlank]
        private array $trashItems = [],
        private ?Location $location = null
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

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): void
    {
        $this->location = $location;
    }
}
