<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\Mapper\SubtreeLimitationMapper;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
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

class SubtreeLimitationMapperTest extends TestCase
{
    public function testMapLimitationValue()
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
        $permissionResolverMock = $this->createMock(PermissionResolver::class);
        $repositoryMock = $this->createMock(Repository::class);

        foreach ($values as $i => $pathString) {
            $query = new LocationQuery([
                'filter' => new Ancestor($pathString),
                'sortClauses' => [new Path()],
            ]);

            $searchServiceMock
                ->expects($this->at($i))
                ->method('findLocations')
                ->with($query)
                ->willReturn($this->createSearchResultsMock($expected[$i]));
        }

        $mapper = new SubtreeLimitationMapper(
            $locationServiceMock,
            $searchServiceMock,
            $permissionResolverMock,
            $repositoryMock
        );
        $result = $mapper->mapLimitationValue(new SubtreeLimitation([
            'limitationValues' => $values,
        ]));

        $this->assertEquals($expected, $result);
    }

    private function createSearchResultsMock($expected)
    {
        $hits = [];
        foreach ($expected as $contentInfo) {
            $locationMock = $this->createMock(Location::class);
            $locationMock
                ->expects($this->atLeastOnce())
                ->method('getContentInfo')
                ->willReturn($contentInfo);

            $hits[] = new SearchHit(['valueObject' => $locationMock]);
        }

        return new SearchResult(['searchHits' => $hits]);
    }
}

class_alias(SubtreeLimitationMapperTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Limitation\Mapper\SubtreeLimitationMapperTest');
