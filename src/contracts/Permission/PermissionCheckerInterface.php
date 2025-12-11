<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Permission;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;

interface PermissionCheckerInterface
{
    /**
     * @param array<mixed> $hasAccess
     *
     * @return array<mixed>
     */
    public function getRestrictions(array $hasAccess, string $class): array;

    /**
     * @param array<mixed>|bool $hasAccess
     */
    public function canCreateInLocation(Location $location, array|bool $hasAccess): bool;
}
