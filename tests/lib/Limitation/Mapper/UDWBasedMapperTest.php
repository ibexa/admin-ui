<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\Mapper\UDWBasedMapper;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Ancestor;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Location\Path;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchHit;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SubtreeLimitation;
use Ibexa\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\AdminUi\Limitation\Mapper\UDWBasedMapper
 */
class UDWBasedMapperTest extends TestCase
{
    public function testMapLimitationValue(): void
    {
        $values = [5, 7, 11];
        $expected = [
            [
                new ContentInfo(['id' => 1]),
                new ContentInfo(['id' => 2]),
                new ContentInfo(['id' => 5]),
            ],
            [
                new ContentInfo(['id' => 1]),
                new ContentInfo(['id' => 2]),
                new ContentInfo(['id' => 7]),
            ],
            [
                new ContentInfo(['id' => 1]),
                new ContentInfo(['id' => 2]),
                new ContentInfo(['id' => 11]),
            ],
        ];

        $locationServiceMock = $this->createMock(LocationService::class);
        $searchServiceMock = $this->createMock(SearchService::class);
        $permissionResolverMock = $this->createMock(PermissionResolver::class);
        $repositoryMock = $this->createMock(Repository::class);

        foreach ($values as $i => $id) {
            $location = new Location([
                'pathString' => '/1/2/' . $id . '/',
            ]);

            $locationServiceMock
                ->expects(self::at($i))
                ->method('loadLocation')
                ->with($id)
                ->willReturn($location);

            $query = new LocationQuery([
                'filter' => new Ancestor($location->pathString),
                'sortClauses' => [new Path()],
            ]);

            $searchServiceMock
                ->expects(self::at($i))
                ->method('findLocations')
                ->with($query)
                ->willReturn($this->createSearchResultsMock($expected[$i]));
        }

        $mapper = new UDWBasedMapper(
            $locationServiceMock,
            $searchServiceMock,
            $permissionResolverMock,
            $repositoryMock
        );
        $result = $mapper->mapLimitationValue(new SubtreeLimitation([
            'limitationValues' => $values,
        ]));

        self::assertEquals($expected, $result);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo[] $expected
     *
     * @phpstan-return \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult<\Ibexa\Core\Repository\Values\Content\Location>
     */
    private function createSearchResultsMock(array $expected): SearchResult
    {
        $hits = [];
        foreach ($expected as $contentInfo) {
            $locationMock = $this->createMock(Location::class);
            $locationMock
                ->expects(self::atLeastOnce())
                ->method('getContentInfo')
                ->willReturn($contentInfo);

            $hits[] = new SearchHit(['valueObject' => $locationMock]);
        }

        /** @phpstan-var \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult<\Ibexa\Core\Repository\Values\Content\Location> */
        return new SearchResult(['searchHits' => $hits]);
    }
}
