<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\Mapper\SubtreeLimitationMapper;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Ancestor;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Location\Path;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchHit;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SubtreeLimitation;
use PHPUnit\Framework\TestCase;

final class SubtreeLimitationMapperTest extends TestCase
{
    public function testMapLimitationValue(): void
    {
        $values = ['/1/2/5/', '/1/2/7/', '/1/2/11/'];
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
        $repositoryMock = $this->createMock(Repository::class);

        foreach ($values as $i => $pathString) {
            $query = new LocationQuery([
                'filter' => new Ancestor($pathString),
                'sortClauses' => [new Path()],
            ]);

            $searchServiceMock
                ->expects(self::at($i))
                ->method('findLocations')
                ->with($query)
                ->willReturn($this->createSearchResultsMock($expected[$i]));
        }

        $mapper = new SubtreeLimitationMapper(
            $locationServiceMock,
            $searchServiceMock,
            $repositoryMock
        );
        $result = $mapper->mapLimitationValue(new SubtreeLimitation([
            'limitationValues' => $values,
        ]));

        self::assertEquals($expected, $result);
    }

    /**
     * @phpstan-param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo[] $expected
     *
     * @phpstan-return \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult<
     *     \Ibexa\Contracts\Core\Repository\Values\Content\Location
     * >
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

        /** @phpstan-var \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult<\Ibexa\Contracts\Core\Repository\Values\Content\Location> */
        return new SearchResult(['searchHits' => $hits]);
    }
}
