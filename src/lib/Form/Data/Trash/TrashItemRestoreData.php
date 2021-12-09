<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Trash;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo add validation
 */
class TrashItemRestoreData
{
    /**
     * @Assert\NotBlank()
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\TrashItem[]
     */
    public $trashItems;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location|null */
    public $location;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\TrashItem[] $trashItems
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     */
    public function __construct(array $trashItems = [], ?Location $location = null)
    {
        $this->trashItems = $trashItems;
        $this->location = $location;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\TrashItem[]
     */
    public function getTrashItems(): array
    {
        return $this->trashItems;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\TrashItem[] $trashItems
     */
    public function setTrashItems(array $trashItems)
    {
        $this->trashItems = $trashItems;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location|null
     */
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     */
    public function setLocation(?Location $location)
    {
        $this->location = $location;
    }
}

class_alias(TrashItemRestoreData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\Trash\TrashItemRestoreData');
