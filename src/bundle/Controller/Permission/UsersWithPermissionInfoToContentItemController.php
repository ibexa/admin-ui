<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\Permission;

use Ibexa\AdminUi\User\Mapper\UsersWithPermissionInfoToContentItemMapper;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\User\UserGroup;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\QueryType\QueryType;
use Ibexa\Rest\Server\Controller;
use LogicException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

final class UsersWithPermissionInfoToContentItemController extends Controller
{
    private ConfigResolverInterface $configResolver;

    private QueryType $userQueryType;

    private SearchService $searchService;

    private UserService $userService;

    private UsersWithPermissionInfoToContentItemMapper $userWithPermissionsMapper;

    private int $limit;

    public function __construct(
        ConfigResolverInterface $configResolver,
        QueryType $userQueryType,
        SearchService $searchService,
        UserService $userService,
        UsersWithPermissionInfoToContentItemMapper $userWithPermissionsMapper,
        int $limit
    ) {
        $this->configResolver = $configResolver;
        $this->userQueryType = $userQueryType;
        $this->searchService = $searchService;
        $this->userService = $userService;
        $this->userWithPermissionsMapper = $userWithPermissionsMapper;
        $this->limit = $limit;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function listAction(
        Request $request,
        ContentInfo $contentInfo,
        string $module,
        string $function,
        ?Location $location = null
    ): JsonResponse {
        $searchQuery = $this->getQuery($request->query);
        $users = $this->searchService->findContentInfo($searchQuery, [], false);
        $targets = null !== $location ? [$location] : [];

        $response = $this->userWithPermissionsMapper->mapSearchResults(
            $users,
            $contentInfo,
            $module,
            $function,
            $targets
        );

        return new JsonResponse($response);
    }

    private function getQuery(ParameterBag $query): Query
    {
        $limit = $query->getInt('limit', $this->limit);
        $offset = $query->getInt('offset');
        $phrase = $query->get('phrase');

        return $this->userQueryType->getQuery(
            [
                'limit' => $limit,
                'offset' => $offset,
                'phrase' => $phrase,
                'section_identifiers' => ['users'],
                'exclude_users_ids' => [$this->getAnonymousUserId()],
                'exclude_paths' => [$this->getUserRegistrationGroupPath()],
            ]
        );
    }

    private function getAnonymousUserId(): int
    {
        return $this->configResolver->getParameter('anonymous_user_id');
    }

    private function getUserRegistrationGroupPath(): string
    {
        $groupId = $this->configResolver->getParameter('user_registration.group_id');

        $userGroup = $this->repository->sudo(
            fn (): UserGroup => $this->userService->loadUserGroup($groupId)
        );

        $location = $userGroup->getContentInfo()->getMainLocation();
        if (null === $location) {
            throw new LogicException('User registration group must have a main location.');
        }

        return $location->getPathString();
    }
}
