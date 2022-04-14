<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ChoiceList\Loader;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentTypeIdentifier;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\ContentName;
use Symfony\Component\Form\ChoiceList\Loader\AbstractChoiceLoader;

final class UserGroupsChoiceLoader extends AbstractChoiceLoader
{
    private Repository $repository;

    private SearchService $searchService;

    private UserService $userService;

    public function __construct(
        Repository $repository,
        SearchService $searchService,
        UserService $userService
    ) {
        $this->repository = $repository;
        $this->searchService = $searchService;
        $this->userService = $userService;
    }

    protected function loadChoices(): array
    {
        return $this->repository->sudo(function () {
            $query = new LocationQuery();
            $query->filter = new ContentTypeIdentifier('user_group');
            $query->offset = 0;
            $query->limit = 100;
            $query->performCount = true;
            $query->sortClauses[] = new ContentName();

            $groups = [];
            do {
                $results = $this->searchService->findContent($query);
                foreach ($results->searchHits as $hit) {
                    $group = $this->userService->loadUserGroup($hit->valueObject->id);
                    $groups[$group->getName()] = $group->id;
                }

                $query->offset += $query->limit;
            } while ($query->offset < $results->totalCount);

            return $groups;
        });
    }
}
