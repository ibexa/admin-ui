<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Service\Role;

use Ibexa\AdminUi\Form\Data\PolicyData;
use Ibexa\AdminUi\Form\Data\RoleAssignmentData;
use Ibexa\AdminUi\Form\Data\RoleData;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentId;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\RoleLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SectionLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SubtreeLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Policy;
use Ibexa\Contracts\Core\Repository\Values\User\Role;
use Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment;
use Ibexa\Core\Repository;
use Ibexa\Core\Repository\SearchService;
use InvalidArgumentException;
use RuntimeException;

readonly class RoleService
{
    public function __construct(
        private Repository\RoleService $roleService,
        private SearchService $searchService
    ) {
    }

    public function getRole(int $id): Role
    {
        return $this->roleService->loadRole($id);
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Role[]
     */
    public function getRoles(): iterable
    {
        return $this->roleService->loadRoles();
    }

    public function createRole(RoleData $data): Role
    {
        $identifier = $data->getIdentifier();
        if ($identifier === null) {
            throw new InvalidArgumentException('Role identifier cannot be null');
        }

        $roleCreateStruct = $this->roleService->newRoleCreateStruct(
            $identifier
        );

        $role = $this->roleService->createRole($roleCreateStruct);
        $this->roleService->publishRoleDraft($role);

        return $role;
    }

    public function updateRole(Role $role, RoleData $data): Role
    {
        $roleUpdateStruct = $this->roleService->newRoleUpdateStruct();
        $roleUpdateStruct->identifier = $data->getIdentifier() ?? $role->identifier;

        $draft = $this->roleService->createRoleDraft($role);
        $this->roleService->updateRoleDraft($draft, $roleUpdateStruct);
        $this->roleService->publishRoleDraft($draft);

        return $draft;
    }

    public function deleteRole(Role $role): void
    {
        $this->roleService->deleteRole($role);
    }

    public function getPolicy(Role $role, int $policyId): ?Policy
    {
        foreach ($role->getPolicies() as $policy) {
            if ($policy->id === $policyId) {
                return $policy;
            }
        }

        return null;
    }

    public function createPolicy(Role $role, PolicyData $data): Role
    {
        $module = $data->getModule();
        $function = $data->getFunction();
        if ($module === null || $function === null) {
            throw new InvalidArgumentException('Policy module and function cannot be null.');
        }

        $policyCreateStruct = $this->roleService->newPolicyCreateStruct(
            $module,
            $function
        );

        $draft = $this->roleService->createRoleDraft($role);
        $this->roleService->addPolicyByRoleDraft($draft, $policyCreateStruct);
        $this->roleService->publishRoleDraft($draft);

        return $draft;
    }

    public function deletePolicy(Role $role, Policy $policy): void
    {
        $draft = $this->roleService->createRoleDraft($role);
        foreach ($draft->getPolicies() as $policyDraft) {
            if ($policyDraft->originalId == $policy->id) {
                $this->roleService->removePolicyByRoleDraft($draft, $policyDraft);
                $this->roleService->publishRoleDraft($draft);

                return;
            }
        }

        throw new RuntimeException("Policy {$policy->id} not found.");
    }

    public function updatePolicy(Role $role, Policy $policy, PolicyData $data): Role
    {
        $policyUpdateStruct = $this->roleService->newPolicyUpdateStruct();
        foreach ($data->getLimitations() as $limitation) {
            if (!empty($limitation->limitationValues)) {
                $policyUpdateStruct->addLimitation($limitation);
            }
        }

        $roleDraft = $this->roleService->createRoleDraft($role);
        foreach ($roleDraft->getPolicies() as $policyDraft) {
            if ($policyDraft->originalId == $policy->id) {
                $this->roleService->updatePolicyByRoleDraft($roleDraft, $policyDraft, $policyUpdateStruct);
                $this->roleService->publishRoleDraft($roleDraft);

                return $roleDraft;
            }
        }

        throw new RuntimeException("Policy {$policy->id} not found.");
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment[]
     */
    public function getRoleAssignments(Role $role): iterable
    {
        return $this->roleService->getRoleAssignments($role);
    }

    public function getRoleAssignment(int $roleAssignmentId): RoleAssignment
    {
        return $this->roleService->loadRoleAssignment($roleAssignmentId);
    }

    public function removeRoleAssignment(RoleAssignment $roleAssignment): void
    {
        $this->roleService->removeRoleAssignment($roleAssignment);
    }

    public function assignRole(Role $role, RoleAssignmentData $data): void
    {
        $users = $data->getUsers();
        $groups = $data->getGroups();

        $sections = $data->getSections();
        $locations = $data->getLocations();

        if (empty($sections) && empty($locations)) {
            // Assign role to user/groups without limitations
            $this->doAssignLimitation($role, $users, $groups);

            return;
        }

        if (!empty($sections)) {
            $limitation = new SectionLimitation();
            $limitation->limitationValues = [];
            foreach ($sections as $section) {
                $limitation->limitationValues[] = $section->id;
            }

            // Assign role to user/groups with section limitations
            $this->doAssignLimitation($role, $users, $groups, $limitation);
        }

        if (!empty($locations)) {
            $limitation = new SubtreeLimitation();
            $limitation->limitationValues = [];

            $query = new LocationQuery();
            $query->filter = new ContentId($locations);

            $result = $this->searchService->findLocations($query);
            foreach ($result->searchHits as $searchHit) {
                /** @var Repository\Values\Content\Location $location */
                $limitation->limitationValues[] = $searchHit->valueObject->pathString;
            }

            // Assign role to user/groups with subtree limitations
            $this->doAssignLimitation($role, $users, $groups, $limitation);
        }
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\User[]|null $users
     * @param \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[]|null $groups
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\LimitationValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function doAssignLimitation(
        Role $role,
        ?array $users = null,
        ?array $groups = null,
        ?RoleLimitation $limitation = null
    ): void {
        if (null !== $users) {
            foreach ($users as $user) {
                $this->roleService->assignRoleToUser($role, $user, $limitation);
            }
        }

        if (null !== $groups) {
            foreach ($groups as $group) {
                $this->roleService->assignRoleToUserGroup($role, $group, $limitation);
            }
        }
    }
}
