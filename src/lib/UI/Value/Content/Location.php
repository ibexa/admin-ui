<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\Content;

use Ibexa\Contracts\Core\Repository\Values\Content\Location as APILocation;
use Ibexa\Core\Repository\Values\Content\Location as CoreLocation;

/**
 * Extends original value object in order to provide additional fields.
 * Takes a standard location instance and retrieves properties from it in addition to the provided properties.
 */
class Location extends CoreLocation
{
    protected int $childCount;

    protected bool $main;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location[] */
    protected array $pathLocations;

    protected bool $userCanManage;

    protected bool $userCanRemove;

    protected bool $userCanEdit;

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct(
        readonly APILocation $location,
        readonly array $properties = []
    ) {
        parent::__construct(get_object_vars($location) + $properties);
    }

    public function canDelete(): bool
    {
        return !$this->main && $this->userCanManage && $this->userCanRemove;
    }

    public function canEdit(): bool
    {
        return $this->userCanEdit;
    }
}
