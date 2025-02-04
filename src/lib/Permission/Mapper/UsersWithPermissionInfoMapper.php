<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Permission\Mapper;

use Ibexa\Contracts\AdminUi\Values\PermissionCheckContext;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Core\Repository\Values\User\UserReference;

/**
 * @phpstan-type TUserData array{
 *     id: int,
 *     name: string,
 *     email: string,
 * }
 * @phpstan-type TPermissionInfoData array{
 *     access: array<TUserData>,
 *     noAccess: array<TUserData>,
 * }
 */
final class UsersWithPermissionInfoMapper
{
    private PermissionResolver $permissionResolver;

    private Repository $repository;

    private UserService $userService;

    public function __construct(
        PermissionResolver $permissionResolver,
        Repository $repository,
        UserService $userService
    ) {
        $this->permissionResolver = $permissionResolver;
        $this->repository = $repository;
        $this->userService = $userService;
    }

    /**
     * @phpstan-return TPermissionInfoData
     *
     * @phpstan-param \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult<\Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo> $searchResult
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function mapSearchResults(
        SearchResult $searchResult,
        PermissionCheckContext $permissionContext,
        string $module,
        string $function
    ): array {
        $currentUserReference = $this->permissionResolver->getCurrentUserReference();

        try {
            return $this->groupByPermissions($searchResult, $permissionContext, $module, $function);
        } finally {
            $this->permissionResolver->setCurrentUserReference($currentUserReference);
        }
    }

    /**
     * @phpstan-return TPermissionInfoData
     *
     * @phpstan-param \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult<\Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo> $searchResult
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function groupByPermissions(
        SearchResult $searchResult,
        PermissionCheckContext $context,
        string $module,
        string $function
    ): array {
        $results = [
            'access' => [],
            'noAccess' => [],
        ];

        foreach ($searchResult as $result) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $userContentInfo */
            $userContentInfo = $result->valueObject;
            $user = $this->loadUser($userContentInfo->getId());
            $userReference = new UserReference($user->getUserId());
            $this->permissionResolver->setCurrentUserReference($userReference);

            $object = $context->getSubject();
            $targets = $context->getTargets();
            $userData = $this->getUserData($user);

            if ($this->permissionResolver->canUser($module, $function, $object, $targets)) {
                $results['access'][] = $userData;
            } else {
                $results['noAccess'][] = $userData;
            }
        }

        return $results;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function loadUser(int $userId): User
    {
        return $this->repository->sudo(
            fn (): User => $this->userService->loadUser($userId)
        );
    }

    /**
     * @phpstan-return TUserData
     */
    private function getUserData(User $user): array
    {
        return [
            'id' => $user->getUserId(),
            'name' => $user->getName() ?? $user->getLogin(),
            'email' => $user->email,
        ];
    }
}
