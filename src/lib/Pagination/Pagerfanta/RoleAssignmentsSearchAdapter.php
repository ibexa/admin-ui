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

final class RoleAssignmentsSearchAdapter implements AdapterInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\RoleService */
    private $roleService;

    /** @var \Ibexa\Contracts\Core\Repository\Values\User\Role */
    private $role;

    /** @var int|null */
    private $assignmentsCount;

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
