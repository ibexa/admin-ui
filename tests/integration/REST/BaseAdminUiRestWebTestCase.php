<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi\REST;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation\RoleLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Role;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Test\Rest\BaseRestWebTestCase;
use Ibexa\Core\MVC\Symfony\Security\UserWrapped;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Requires \Ibexa\Tests\Integration\AdminUi\AdminUiIbexaTestKernel kernel.
 *
 * @phpstan-type TPoliciesData array<string, \Ibexa\Contracts\Core\Repository\Values\User\Limitation[]>
 *
 * @see \Ibexa\Tests\Integration\AdminUi\AdminUiIbexaTestKernel
 */
abstract class BaseAdminUiRestWebTestCase extends BaseRestWebTestCase
{
    protected function getSchemaFileBasePath(string $resourceType, string $format): string
    {
        return dirname(__DIR__) . '/Resources/REST/Schemas/' . $resourceType;
    }

    protected static function getSnapshotDirectory(): ?string
    {
        return dirname(__DIR__) . '/Resources/REST/Snapshots';
    }

    /**
     * @phpstan-param TPoliciesData $policiesData
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    protected function createUserWithPolicies(
        string $login,
        array $policiesData,
        ?RoleLimitation $roleLimitation = null
    ): User {
        $ibexaTestCore = $this->getIbexaTestCore();
        $userService = $ibexaTestCore->getUserService();
        $roleService = $ibexaTestCore->getRoleService();

        $userCreateStruct = $userService->newUserCreateStruct(
            $login,
            "$login@test.local",
            $login,
            'eng-GB'
        );
        $userCreateStruct->setField('first_name', $login);
        $userCreateStruct->setField('last_name', $login);
        $user = $userService->createUser($userCreateStruct, [$userService->loadUserGroup(4)]);

        $role = $this->createRoleWithPolicies(uniqid('role_for_' . $login . '_', true), $policiesData);
        $roleService->assignRoleToUser($role, $user, $roleLimitation);

        return $user;
    }

    /**
     * @phpstan-param TPoliciesData $policiesData
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    protected function createRoleWithPolicies(string $roleName, array $policiesData): Role
    {
        $roleService = $this->getIbexaTestCore()->getRoleService();

        $roleCreateStruct = $roleService->newRoleCreateStruct($roleName);
        foreach ($policiesData as $moduleFunction => $limitations) {
            [$module, $function] = explode('/', $moduleFunction);
            $policyCreateStruct = $roleService->newPolicyCreateStruct($module, $function);

            foreach ($limitations as $limitation) {
                $policyCreateStruct->addLimitation($limitation);
            }
            $roleCreateStruct->addPolicy($policyCreateStruct);
        }

        $roleDraft = $roleService->createRole($roleCreateStruct);
        $roleService->publishRoleDraft($roleDraft);

        return $roleService->loadRole($roleDraft->id);
    }

    protected function loginAsUser(User $ibexaUser): void
    {
        $this->client->loginUser($this->mockSymfonyUser($ibexaUser));
    }

    private function mockSymfonyUser(User $ibexaUser): UserInterface
    {
        $symfonyUser = $this->createMock(UserInterface::class);
        $symfonyUser->method('getRoles')->willReturn(['ROLE_USER']);
        $symfonyUser->method('getUsername')->willReturn($ibexaUser->login);

        return new UserWrapped($symfonyUser, $ibexaUser);
    }
}
