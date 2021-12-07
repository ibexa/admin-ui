<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Role;

use Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment;

class RoleAssignmentDeleteData
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment */
    private $roleAssignment;

    public function __construct(?RoleAssignment $roleAssignment = null)
    {
        $this->roleAssignment = $roleAssignment;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment
     */
    public function getRoleAssignment(): ?RoleAssignment
    {
        return $this->roleAssignment;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment $roleAssignment
     */
    public function setRoleAssignment(RoleAssignment $roleAssignment)
    {
        $this->roleAssignment = $roleAssignment;
    }
}

class_alias(RoleAssignmentDeleteData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\Role\RoleAssignmentDeleteData');
