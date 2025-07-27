<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;

class LocationTrashData
{
    private ?Location $location;

    private ?array $trashOptions;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     * @param array|null $trashOptions
     */
    public function __construct(
        ?Location $location = null,
        ?array $trashOptions = null
    ) {
        $this->location = $location;
        $this->trashOptions = $trashOptions;
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
    public function setLocation(?Location $location): void
    {
        $this->location = $location;
    }

    /**
     * @return array|null
     */
    public function getTrashOptions(): ?array
    {
        return $this->trashOptions;
    }

    /**
     * @param array|null $trashOptions
     */
    public function setTrashOptions(?array $trashOptions): void
    {
        $this->trashOptions = $trashOptions;
    }
}
