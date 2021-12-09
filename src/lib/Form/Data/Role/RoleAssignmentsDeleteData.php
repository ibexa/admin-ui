<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Role;

use Ibexa\Contracts\Core\Repository\Values\User\Role;

/**
 * @todo Add validation
 */
class RoleAssignmentsDeleteData
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\User\Role|null */
    protected $role;

    /** @var array|null */
    protected $roleAssignments;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Role|null $role
     * @param array|null $roleAssignments
     */
    public function __construct(?Role $role = null, array $roleAssignments = [])
    {
        $this->role = $role;
        $this->roleAssignments = $roleAssignments;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Role|null
     */
    public function getRole(): ?Role
    {
        return $this->role;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Role|null $role
     */
    public function setRole(?Role $role)
    {
        $this->role = $role;
    }

    /**
     * @return array|null
     */
    public function getRoleAssignments(): ?array
    {
        return $this->roleAssignments;
    }

    /**
     * @param array|null $roleAssignments
     */
    public function setRoleAssignments(?array $roleAssignments)
    {
        $this->roleAssignments = $roleAssignments;
    }
}

class_alias(RoleAssignmentsDeleteData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\Role\RoleAssignmentsDeleteData');
