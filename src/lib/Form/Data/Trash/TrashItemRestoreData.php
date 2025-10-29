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
class TrashItemRestoreData
{
    /**
     * @Assert\NotBlank()
     *
     * @var TrashItem[]
     */
    public $trashItems;

    /** @var Location|null */
    public $location;

    /**
     * @param TrashItem[] $trashItems
     * @param Location|null $location
     */
    public function __construct(
        array $trashItems = [],
        ?Location $location = null
    ) {
        $this->trashItems = $trashItems;
        $this->location = $location;
    }

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
    public function setTrashItems(array $trashItems)
    {
        $this->trashItems = $trashItems;
    }

    /**
     * @return Location|null
     */
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    /**
     * @param Location|null $location
     */
    public function setLocation(?Location $location)
    {
        $this->location = $location;
    }
}

class_alias(TrashItemRestoreData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\Trash\TrashItemRestoreData');
