<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\Policy;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @template-extends \Ibexa\Bundle\AdminUi\ValueResolver\AbstractValueResolver<\Ibexa\Contracts\Core\Repository\Values\User\Policy>
 */
final class PolicyValueResolver extends AbstractValueResolver
{
    private const string ATTRIBUTE_ROLE_ID = 'roleId';
    private const string ATTRIBUTE_POLICY_ID = 'policyId';

    public function __construct(
        private readonly RoleService $roleService
    ) {
    }

    protected function getRequestAttributes(): array
    {
        return [
            self::ATTRIBUTE_ROLE_ID,
            self::ATTRIBUTE_POLICY_ID,
        ];
    }

    protected function getClass(): string
    {
        return Policy::class;
    }

    protected function validateValue(string $value): bool
    {
        return is_numeric($value);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    protected function load(array $key): object
    {
        $roleId = (int)$key[self::ATTRIBUTE_ROLE_ID];
        $policyId = (int)$key[self::ATTRIBUTE_POLICY_ID];

        $role = $this->roleService->loadRole($roleId);
        foreach ($role->getPolicies() as $policy) {
            if ($policy->id === $policyId) {
                return $policy;
            }
        }

        throw new NotFoundHttpException("Policy draft $policyId not found.");
    }
}
