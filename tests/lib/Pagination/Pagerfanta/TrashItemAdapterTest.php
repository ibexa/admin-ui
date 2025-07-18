<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Pagination\Pagerfanta;

use Ibexa\AdminUi\Pagination\Pagerfanta\TrashItemAdapter;
use Ibexa\Contracts\Core\Repository\TrashService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as APIContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Core\Repository\Values\Content\Trash\SearchResult;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TrashItemAdapterTest extends TestCase
{
    protected TrashService&MockObject $trashService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->trashService = $this->createMock(TrashService::class);
    }

    /**
     * Returns the adapter to test.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query $query
     * @param \Ibexa\Contracts\Core\Repository\TrashService $trashService
     *
     * @return \Ibexa\AdminUi\Pagination\Pagerfanta\TrashItemAdapter
     */
    protected function getAdapter(Query $query, TrashService $trashService): TrashItemAdapter
    {
        return new TrashItemAdapter($query, $trashService);
    }

    public function testGetNbResults(): void
    {
        $nbResults = 123;
        $query = $this->createMockedQuery();

        // Count query will necessarily have a 0 limit.
        $countQuery = clone $query;
        $countQuery->limit = 0;

        $searchResult = new SearchResult(['totalCount' => $nbResults]);
        $this->trashService
            ->expects(self::once())
            ->method('findTrashItems')
            ->with(self::equalTo($countQuery))
            ->willReturn($searchResult);

        $adapter = $this->getAdapter($query, $this->trashService);
        self::assertSame($nbResults, $adapter->getNbResults());

        // Running a 2nd time to ensure SearchService::findContent() is called only once.
        self::assertSame($nbResults, $adapter->getNbResults());
    }

    public function testGetSlice(): void
    {
        $offset = 20;
        $limit = 25;
        $nbResults = 123;

        $query = $this->createMockedQuery();

        // Injected query is being cloned to modify offset/limit,
        // so we need to do the same here for our assertions.
        $searchQuery = clone $query;
        $searchQuery->offset = $offset;
        $searchQuery->limit = $limit;
        $searchQuery->performCount = false;

        $items = [];
        for ($i = 0; $i < $limit; ++$i) {
            $content = $this->getMockForAbstractClass(APIContent::class);
            $items[] = $content;
        }

        $searchResult = new SearchResult(['items' => $items, 'totalCount' => $nbResults]);

        $this->trashService
            ->expects(self::once())
            ->method('findTrashItems')
            ->with(self::equalTo($searchQuery))
            ->willReturn($searchResult);

        $adapter = $this->getAdapter($query, $this->trashService);

        self::assertSame($items, $adapter->getSlice($offset, $limit));
        self::assertSame($nbResults, $adapter->getNbResults());
        // Running a 2nd time to ensure SearchService::findContent() is called only once.
        self::assertSame($nbResults, $adapter->getNbResults());
    }

    private function createMockedQuery(): Query
    {
        $query = new Query();
        $query->query = $this->createMock(CriterionInterface::class);
        $query->sortClauses = [$this->createMock(SortClause::class)];

        return $query;
    }
}
