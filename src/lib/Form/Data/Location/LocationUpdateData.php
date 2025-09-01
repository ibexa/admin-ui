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
    protected ?int $sortField;

    protected ?int $sortOrder;

    public function __construct(protected ?Location $location = null)
    {
        $this->sortField = $location->sortField ?? null;
        $this->sortOrder = $location->sortOrder ?? null;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): void
    {
        $this->location = $location;
    }

    public function setSortField(?int $sortField): self
    {
        $this->sortField = $sortField;

        return $this;
    }

    public function getSortField(): ?int
    {
        return $this->sortField;
    }

    public function setSortOrder(?int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }
}
