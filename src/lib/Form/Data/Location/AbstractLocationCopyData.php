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
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location|null */
    protected $location;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location|null */
    protected $newParentLocation;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $newParentLocation
     */
    public function __construct(?Location $location = null, Location $newParentLocation = null)
    {
        $this->location = $location;
        $this->newParentLocation = $newParentLocation;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location||null
     */
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location||null $location
     */
    public function setLocation(?Location $location)
    {
        $this->location = $location;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location||null
     */
    public function getNewParentLocation(): ?Location
    {
        return $this->newParentLocation;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location||null $newParentLocation
     */
    public function setNewParentLocation(?Location $newParentLocation)
    {
        $this->newParentLocation = $newParentLocation;
    }
}
