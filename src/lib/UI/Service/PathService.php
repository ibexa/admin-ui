<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

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
final readonly class PathService
{
    public function __construct(private SearchService $searchService)
    {
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
     */
    public function loadPathLocations(Location $location): array
    {
        $locationQuery = new LocationQuery([
            'filter' => new Ancestor($location->getPathString()),
            'sortClauses' => [new Path()],
        ]);

        $searchResult = $this->searchService->findLocations($locationQuery);

        return array_map(static function (SearchHit $searchHit): Location {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
            $location = $searchHit->valueObject;

            return $location;
        }, $searchResult->searchHits);
    }
}
