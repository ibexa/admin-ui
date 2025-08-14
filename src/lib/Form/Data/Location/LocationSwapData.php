<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;

/**
 * @todo add validation
 */
class LocationSwapData
{
    protected ?Location $currentLocation;

    protected ?Location $newLocation;

    public function __construct(?Location $currentLocation = null, ?Location $newLocation = null)
    {
        $this->currentLocation = $currentLocation;
        $this->newLocation = $newLocation;
    }

    public function getCurrentLocation(): ?Location
    {
        return $this->currentLocation;
    }

    public function setCurrentLocation(?Location $currentLocation): void
    {
        $this->currentLocation = $currentLocation;
    }

    public function getNewLocation(): ?Location
    {
        return $this->newLocation;
    }

    public function setNewLocation(?Location $newLocation): void
    {
        $this->newLocation = $newLocation;
    }
}
