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
final class LocationSwapData
{
    public function __construct(
        private ?Location $currentLocation = null,
        private ?Location $newLocation = null
    ) {
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
