<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Permission\Mapper;

use Ibexa\Contracts\AdminUi\Values\PermissionCheckContext;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
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
 *     no_access: array<TUserData>,
 * }
 */
final class UsersWithPermissionInfoMapper
{
    private PermissionResolver $permissionResolver;

    private UserService $userService;

    public function __construct(
        UserService $userService,
        PermissionResolver $permissionResolver
    ) {
        $this->userService = $userService;
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * @phpstan-return TPermissionInfoData
     *
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

        $results = $this->groupByPermissions($searchResult, $permissionContext, $module, $function);

        $this->permissionResolver->setCurrentUserReference($currentUserReference);

        return $results;
    }

    /**
     * @phpstan-return TPermissionInfoData
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
            'no_access' => [],
        ];

        foreach ($searchResult as $result) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $userContentInfo */
            $userContentInfo = $result->valueObject;

            $user = $this->userService->loadUser($userContentInfo->getId());
            $userReference = new UserReference($user->getUserId());
            $userData = $this->getUserData($user);

            $this->permissionResolver->setCurrentUserReference($userReference);

            $object = $context->getSubject();
            $targets = $context->getTargets();

            if ($this->permissionResolver->canUser($module, $function, $object, $targets)) {
                $results['access'][] = $userData;
            } else {
                $results['no_access'][] = $userData;
            }
        }

        return $results;
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
