<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\UniversalDiscovery;

use Ibexa\Rest\Value;

/**
 * @phpstan-import-type PermissionRestrictions from \Ibexa\AdminUi\REST\Value\UniversalDiscovery\LocationData
 *
 * @phpstan-type LocationList array<
 *      array{
 *          location: \Ibexa\Contracts\Core\Repository\Values\Content\Location,
 *          permissions: PermissionRestrictions,
 *      }
 * >
 */
final class LocationListData extends Value
{
    /** @phpstan-var LocationList */
    private array $locationList;

    /**
     * @phpstan-param LocationList $locationList
     */
    public function __construct(array $locationList)
    {
        $this->locationList = $locationList;
    }

    /**
     * @phpstan-return LocationList
     */
    public function getLocationList(): array
    {
        return $this->locationList;
    }
}
