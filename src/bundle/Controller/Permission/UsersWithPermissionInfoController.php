<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\Permission;

use Ibexa\AdminUi\Permission\Mapper\UsersWithPermissionInfoMapper;
use Ibexa\AdminUi\Permission\PermissionCheckContextResolverInterface;
use Ibexa\Contracts\Core\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Core\QueryType\QueryType;
use Ibexa\Rest\Server\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

final class UsersWithPermissionInfoController extends Controller
{
    private const PARAM_LIMIT = 'limit';
    private const PARAM_OFFSET = 'offset';

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

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function getQuery(
        ParameterBag $query,
        ?Query\Criterion $criteria
    ): Query {
        $parameters = [
            'phrase' => $query->get('phrase'),
            'extra_criteria' => $criteria,
            'limit' => $this->limit,
        ];

        if ($query->has(self::PARAM_LIMIT)) {
            $limit = $query->getInt(self::PARAM_LIMIT);

            if ($limit <= 0) {
                throw new InvalidArgumentException(
                    self::PARAM_LIMIT,
                    'Value should be greater than zero'
                );
            }

            $parameters[self::PARAM_LIMIT] = $limit;
        }

        if ($query->has(self::PARAM_OFFSET)) {
            $offset = $query->getInt(self::PARAM_OFFSET);

            if ($offset < 0) {
                throw new InvalidArgumentException(
                    self::PARAM_OFFSET,
                    'Value should be greater or equal zero'
                );
            }

            $parameters[self::PARAM_OFFSET] = $offset;
        }

        return $this->userQueryType->getQuery($parameters);
    }
}
