<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\Role;

/**
 * @phpstan-extends \Ibexa\Bundle\AdminUi\ValueResolver\AbstractValueResolver<\Ibexa\Contracts\Core\Repository\Values\User\Role>
 */
final class RoleValueResolver extends AbstractValueResolver
{
    private const string ATTRIBUTE_ROLE_ID = 'roleId';

    public function __construct(
        private readonly RoleService $roleService
    ) {
    }

    protected function getRequestAttributes(): array
    {
        return [self::ATTRIBUTE_ROLE_ID];
    }

    protected function getClass(): string
    {
        return Role::class;
    }

    protected function validateValue(string $value): bool
    {
        return is_numeric($value);
    }

    protected function load(array $key): object
    {
        return $this->roleService->loadRole(
            (int)$key[self::ATTRIBUTE_ROLE_ID]
        );
    }
}
