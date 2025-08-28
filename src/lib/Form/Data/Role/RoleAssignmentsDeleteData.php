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
final class RoleAssignmentsDeleteData
{
    /**
     * @param array<int, mixed>|null $roleAssignments
     */
    public function __construct(
        private ?Role $role = null,
        private ?array $roleAssignments = []
    ) {
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): void
    {
        $this->role = $role;
    }

    /**
     * @return array<int, mixed>|null
     */
    public function getRoleAssignments(): ?array
    {
        return $this->roleAssignments;
    }

    /**
     * @param array<int, mixed>|null $roleAssignments
     */
    public function setRoleAssignments(?array $roleAssignments): void
    {
        $this->roleAssignments = $roleAssignments;
    }
}
