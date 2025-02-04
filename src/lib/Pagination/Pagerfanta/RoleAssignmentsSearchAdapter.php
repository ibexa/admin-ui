<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\Role;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @implements \Pagerfanta\Adapter\AdapterInterface<\Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment>
 */
final class RoleAssignmentsSearchAdapter implements AdapterInterface
{
    private RoleService $roleService;

    private Role $role;

    private ?int $assignmentsCount;

    public function __construct(RoleService $roleService, Role $role, ?int $assignmentsCount = null)
    {
        $this->roleService = $roleService;
        $this->role = $role;
        $this->assignmentsCount = $assignmentsCount;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function getNbResults(): int
    {
        /** @phpstan-var int<0, max> */
        return $this->assignmentsCount ?: $this->roleService->countRoleAssignments($this->role);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function getSlice($offset, $length): iterable
    {
        return $this->roleService->loadRoleAssignments($this->role, $offset, $length);
    }
}
