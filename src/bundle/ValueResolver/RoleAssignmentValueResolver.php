<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment;

/**
 * @template-extends \Ibexa\Bundle\AdminUi\ValueResolver\AbstractValueResolver<\Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment>
 */
final class RoleAssignmentValueResolver extends AbstractValueResolver
{
    private const string ATTRIBUTE_ROLE_ASSIGNMENT_ID = 'roleAssignmentId';

    public function __construct(
        private readonly RoleService $roleService
    ) {
    }

    protected function getRequestAttributes(): array
    {
        return [
            self::ATTRIBUTE_ROLE_ASSIGNMENT_ID,
        ];
    }

    protected function getClass(): string
    {
        return RoleAssignment::class;
    }

    protected function load(array $key): object
    {
        return $this->roleService->loadRoleAssignment(
            (int)$key[self::ATTRIBUTE_ROLE_ASSIGNMENT_ID]
        );
    }
}
