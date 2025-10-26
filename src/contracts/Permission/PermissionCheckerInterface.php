<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\AdminUi\Permission;

use Ibexa\AdminUi\Permission\LimitationResolverInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\User\LookupLimitationResult;

interface PermissionCheckerInterface
{
    public function getRestrictions(
        array $hasAccess,
        string $class
    ): array;

    /**
     * @param array|bool $hasAccess
     */
    public function canCreateInLocation(
        Location $location,
        $hasAccess
    ): bool;

    /**
     * @throws BadStateException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     *
     * @internal
     *
     * @deprecated 4.6.0 The "\Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface::getContentCreateLimitations()" method is deprecated, will be removed in 5.0.
     * Use { @see \Ibexa\AdminUi\Permission\LimitationResolverInterface::getContentCreateLimitations() } instead.
     */
    public function getContentCreateLimitations(Location $parentLocation): LookupLimitationResult;

    /**
     * @throws BadStateException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     *
     * @internal
     *
     * @deprecated 4.6.0 The "\Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface::getContentUpdateLimitations()" method is deprecated, will be removed in 5.0.
     * Use { @see LimitationResolverInterface::getContentUpdateLimitations } instead.
     */
    public function getContentUpdateLimitations(Location $location): LookupLimitationResult;
}

class_alias(PermissionCheckerInterface::class, 'EzSystems\EzPlatformAdminUi\Permission\PermissionCheckerInterface');
