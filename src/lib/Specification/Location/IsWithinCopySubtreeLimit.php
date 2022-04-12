<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Location;

use Ibexa\AdminUi\Specification\AbstractSpecification;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;

class IsWithinCopySubtreeLimit extends AbstractSpecification
{
    /** @var int */
    private $copyLimit;

    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $searchService;

    /**
     * @param int $copyLimit
     * @param \Ibexa\Contracts\Core\Repository\SearchService $searchService
     */
    public function __construct(
        int $copyLimit,
        SearchService $searchService
    ) {
        $this->copyLimit = $copyLimit;
        $this->searchService = $searchService;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $item
     *
     * @return bool
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function isSatisfiedBy($item): bool
    {
        if ($this->copyLimit === -1) {
            return true;
        }

        if ($this->copyLimit === 0) {
            return false;
        }

        $query = new LocationQuery([
            'filter' => new Criterion\Subtree($item->pathString),
            'limit' => 0,
        ]);

        $searchResults = $this->searchService->findLocations($query);

        if ($this->copyLimit >= $searchResults->totalCount) {
            return true;
        }

        return false;
    }
}

class_alias(IsWithinCopySubtreeLimit::class, 'EzSystems\EzPlatformAdminUi\Specification\Location\IsWithinCopySubtreeLimit');
