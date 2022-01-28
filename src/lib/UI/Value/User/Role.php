<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\User;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation\RoleLimitation as APIRoleLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Role as APIRole;
use Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment;

class Role extends RoleAssignment
{
    /**
     * the limitation of this role assignment.
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\User\Limitation\RoleLimitation|null
     */
    protected $limitation;

    /**
     * the role which is assigned to the user.
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\User\Role
     */
    protected $role;

    /**
     * Returns the limitation of the user role assignment.
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Limitation\RoleLimitation|null
     */
    public function getRoleLimitation(): ?APIRoleLimitation
    {
        return $this->limitation;
    }

    /**
     * Returns the role to which the user is assigned to.
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Role
     */
    public function getRole(): APIRole
    {
        return $this->role;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment $roleAssignment
     * @param array $properties
     */
    public function __construct(RoleAssignment $roleAssignment, array $properties = [])
    {
        parent::__construct(get_object_vars($roleAssignment) + $properties);

        $this->role = $roleAssignment->role;
        $this->limitation = $roleAssignment->limitation;
    }
}

class_alias(Role::class, 'EzSystems\EzPlatformAdminUi\UI\Value\User\Role');
