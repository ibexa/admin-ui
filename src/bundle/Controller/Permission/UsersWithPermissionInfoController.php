<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\Permission;

use Ibexa\AdminUi\Permission\Mapper\UsersWithPermissionInfoMapper;
use Ibexa\AdminUi\Permission\PermissionCheckContextResolverInterface;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Core\QueryType\QueryType;
use Ibexa\Rest\Server\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

final class UsersWithPermissionInfoController extends Controller
{
    private QueryType $userQueryType;

    private PermissionCheckContextResolverInterface $permissionCheckContextResolver;

    private SearchService $searchService;

    private UsersWithPermissionInfoMapper $userWithPermissionsMapper;

    private int $limit;

    public function __construct(
        QueryType $userQueryType,
        PermissionCheckContextResolverInterface $permissionCheckContextResolver,
        SearchService $searchService,
        UsersWithPermissionInfoMapper $userWithPermissionsMapper,
        int $limit
    ) {
        $this->userQueryType = $userQueryType;
        $this->permissionCheckContextResolver = $permissionCheckContextResolver;
        $this->searchService = $searchService;
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
        string $module,
        string $function
    ): JsonResponse {
        $context = $this->permissionCheckContextResolver->resolve($module, $function, $request);
        $searchQuery = $this->getQuery(
            $request->query,
            $context->getCriteria()
        );
        $users = $this->searchService->findContentInfo($searchQuery, [], false);

        $response = $this->userWithPermissionsMapper->mapSearchResults(
            $users,
            $context,
            $module,
            $function
        );

        return new JsonResponse($response);
    }

    private function getQuery(
        ParameterBag $query,
        ?Query\Criterion $criteria
    ): Query {
        $parameters = [
            'limit' => $query->getInt('limit', $this->limit),
            'offset' => $query->getInt('offset'),
            'phrase' => $query->get('phrase'),
            'extra_criteria' => $criteria,
        ];

        return $this->userQueryType->getQuery($parameters);
    }
}
