<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\User\Mapper;

use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Core\Repository\Values\User\UserReference;

/**
 * @phpstan-type TUserData array{
 *      access: array{
 *          name: string,
 *          email: string,
 *      }|array{},
 *      no_access: array{
 *         name: string,
 *         email: string,
 *      }|array{},
 *  }
 */
final class UsersWithPermissionInfoToContentItemMapper
{
    private PermissionResolver $permissionResolver;

    private UserService  $userService;

    public function __construct(
        UserService $userService,
        PermissionResolver $permissionResolver
    ) {
        $this->userService = $userService;
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * @param array<\Ibexa\Contracts\Core\Repository\Values\ValueObject> $targets
     *
     * @phpstan-return TUserData
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function mapSearchResults(
        SearchResult $searchResult,
        ContentInfo $contentInfo,
        string $module,
        string $function,
        array $targets = []
    ): array {
        $currentUserReference = $this->permissionResolver->getCurrentUserReference();

        $results = $this->groupByPermissions($searchResult, $contentInfo, $module, $function, $targets);

        $this->permissionResolver->setCurrentUserReference($currentUserReference);

        return $results;
    }

    /**
     * @param array<\Ibexa\Contracts\Core\Repository\Values\ValueObject> $targets
     *
     * @phpstan-return TUserData
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function groupByPermissions(
        SearchResult $searchResult,
        ContentInfo $contentInfo,
        string $module,
        string $function,
        array $targets = []
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

            if ($this->permissionResolver->canUser($module, $function, $contentInfo, $targets)) {
                $results['access'][] = $userData;
            } else {
                $results['no_access'][] = $userData;
            }
        }

        return $results;
    }

    /**
     * @return array{
     *     name: string,
     *     email: string,
     * }
     */
    private function getUserData(User $user): array
    {
        return [
            'name' => $user->getName() ?? $user->getLogin(),
            'email' => $user->email,
        ];
    }
}
