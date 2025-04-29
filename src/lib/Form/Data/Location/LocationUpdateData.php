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
class LocationUpdateData
{
    protected ?Location $location;

    /** @var string|null */
    protected $sortField;

    /** @var string|null */
    protected $sortOrder;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     */
    public function __construct(?Location $location = null)
    {
        $this->location = $location;
        $this->sortField = $location->sortField ?? null;
        $this->sortOrder = $location->sortOrder ?? null;
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
     * @param int|null $sortField
     *
     * @return LocationUpdateData
     */
    public function setSortField(int $sortField): self
    {
        $this->sortField = $sortField;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSortField(): ?int
    {
        return $this->sortField;
    }

    /**
     * @param int|null $sortOrder
     *
     * @return LocationUpdateData
     */
    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }
}
