<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\UI\Service;

use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Ancestor;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Location\Path;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchHit;

/**
 * Service for loading path information.
 *
 * @internal
 */
class PathService
{
    /** @var \Ibexa\Contracts\Core\Repository\SearchService */
    private $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Load path locations.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
     */
    public function loadPathLocations(Location $location)
    {
        $locationQuery = new LocationQuery([
            'filter' => new Ancestor($location->pathString),
            'sortClauses' => [new Path()],
        ]);

        $searchResult = $this->searchService->findLocations($locationQuery);

        return array_map(static function (SearchHit $searchHit) {
            return $searchHit->valueObject;
        }, $searchResult->searchHits);
    }
}

class_alias(PathService::class, 'EzSystems\EzPlatformAdminUi\UI\Service\PathService');
