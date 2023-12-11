<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\User;

use Ibexa\AdminUi\Specification\UserProfile\IsProfileAvailable;
use Ibexa\AdminUi\UserProfile\UserProfileConfigurationInterface;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\User\Role;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Symfony\Component\HttpFoundation\Response;

final class ProfileViewController extends Controller
{
    private Repository $repository;

    private UserService $userService;

    private RoleService $roleService;

    private PermissionResolver $permissionResolver;

    private UserProfileConfigurationInterface $configuration;

    public function __construct(
        Repository $repository,
        UserService $userService,
        RoleService $roleService,
        PermissionResolver $permissionResolver,
        UserProfileConfigurationInterface $configuration
    ) {
        $this->repository = $repository;
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->permissionResolver = $permissionResolver;
        $this->configuration = $configuration;
    }

    public function viewAction(int $userId): Response
    {
        $user = $this->userService->loadUser($userId);
        if (!$this->isUserProfileAvailable($user)) {
            throw $this->createNotFoundException();
        }

        $canEditProfile = $this->permissionResolver->canUser('user', 'selfedit', $user);

        return $this->render(
            '@ibexadesign/account/profile/view.html.twig',
            [
                'user' => $user,
                'roles' => $this->getUserRoles($user),
                'field_groups' => $this->configuration->getFieldGroups(),
                'can_edit_profile' => $canEditProfile,
            ]
        );
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Role[]
     */
    private function getUserRoles(User $user): iterable
    {
        if ($this->permissionResolver->hasAccess('role', 'read') !== true) {
            return [];
        }

        /** @var \Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment[] $assignments */
        $assignments = $this->repository->sudo(function () use ($user): iterable {
            return $this->roleService->getRoleAssignmentsForUser($user, true);
        });

        $roles = [];
        foreach ($assignments as $assignment) {
            $role = $assignment->getRole();
            if (!array_key_exists($role->id, $roles)) {
                $roles[$role->id] = $role;
            }
        }

        usort($roles, static function (Role $roleA, Role $roleB): int {
            return strcmp($roleA->identifier, $roleB->identifier);
        });

        return $roles;
    }

    private function isUserProfileAvailable(User $user): bool
    {
        return (new IsProfileAvailable($this->configuration))->isSatisfiedBy($user);
    }
}
