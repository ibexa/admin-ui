<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Role;

use Ibexa\Contracts\Core\Repository\Values\User\Role;

class RoleDeleteData
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\User\Role */
    private $role;

    public function __construct(?Role $role = null)
    {
        $this->role = $role;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Role
     */
    public function getRole(): ?Role
    {
        return $this->role;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Role $role
     */
    public function setRole(Role $role)
    {
        $this->role = $role;
    }
}

class_alias(RoleDeleteData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\Role\RoleDeleteData');
