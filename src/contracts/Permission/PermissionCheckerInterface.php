<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\AdminUi\Permission;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\User\LookupLimitationResult;

interface PermissionCheckerInterface
{
    public function getRestrictions(array $hasAccess, string $class): array;

    /**
     * @param array|bool $hasAccess
     */
    public function canCreateInLocation(Location $location, $hasAccess): bool;
}
