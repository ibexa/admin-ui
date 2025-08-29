<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Role;

use Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment;

final class RoleAssignmentDeleteData
{
    public function __construct(private ?RoleAssignment $roleAssignment = null)
    {
    }

    public function getRoleAssignment(): ?RoleAssignment
    {
        return $this->roleAssignment;
    }

    public function setRoleAssignment(?RoleAssignment $roleAssignment): void
    {
        $this->roleAssignment = $roleAssignment;
    }
}
