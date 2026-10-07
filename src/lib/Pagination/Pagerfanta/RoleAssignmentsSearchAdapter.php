<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\Role;
use Pagerfanta\Adapter\AdapterInterface;

final class RoleAssignmentsSearchAdapter implements AdapterInterface
{
    /** @var RoleService */
    private $roleService;

    /** @var Role */
    private $role;

    /** @var int|null */
    private $assignmentsCount;

    public function __construct(
        RoleService $roleService,
        Role $role,
        ?int $assignmentsCount = null
    ) {
        $this->roleService = $roleService;
        $this->role = $role;
        $this->assignmentsCount = $assignmentsCount;
    }

    /**
     * @throws BadStateException
     * @throws InvalidArgumentException
     * @throws UnauthorizedException
     */
    public function getNbResults(): int
    {
        return $this->assignmentsCount ?: $this->roleService->countRoleAssignments($this->role);
    }

    /**
     * @throws BadStateException
     * @throws InvalidArgumentException
     * @throws UnauthorizedException
     */
    public function getSlice(
        $offset,
        $length
    ): iterable {
        return $this->roleService->loadRoleAssignments($this->role, $offset, $length);
    }
}
