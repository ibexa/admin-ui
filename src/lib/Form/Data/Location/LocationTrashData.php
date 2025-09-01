<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;

final class LocationTrashData
{
    /**
     * @param array<string, mixed>|null $trashOptions
     */
    public function __construct(
        private ?Location $location = null,
        private ?array $trashOptions = null
    ) {
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): void
    {
        $this->location = $location;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getTrashOptions(): ?array
    {
        return $this->trashOptions;
    }

    /**
     * @param array<string, mixed>|null $trashOptions
     */
    public function setTrashOptions(?array $trashOptions): void
    {
        $this->trashOptions = $trashOptions;
    }
}
