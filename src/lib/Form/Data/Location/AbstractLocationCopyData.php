<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;

abstract class AbstractLocationCopyData
{
    protected ?Location $location;

    protected ?Location $newParentLocation;

    public function __construct(?Location $location = null, ?Location $newParentLocation = null)
    {
        $this->location = $location;
        $this->newParentLocation = $newParentLocation;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): void
    {
        $this->location = $location;
    }

    public function getNewParentLocation(): ?Location
    {
        return $this->newParentLocation;
    }

    public function setNewParentLocation(?Location $newParentLocation): void
    {
        $this->newParentLocation = $newParentLocation;
    }
}
