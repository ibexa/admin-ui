<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Pagination\Pagerfanta;

use Ibexa\AdminUi\Pagination\Pagerfanta\URLUsagesAdapter;
use Ibexa\Contracts\Core\Repository\URLService;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchHit;
use Ibexa\Contracts\Core\Repository\Values\URL\URL;
use Ibexa\Contracts\Core\Repository\Values\URL\UsageSearchResult;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class URLUsagesAdapterTest extends TestCase
{
    private URLService&MockObject $urlService;

    protected function setUp(): void
    {
        $this->urlService = $this->createMock(URLService::class);
    }

    public function testGetNbResults(): void
    {
        $url = $this->createMock(URL::class);

        $searchResults = new UsageSearchResult([
            'items' => [],
            'totalCount' => 10,
        ]);

        $this->urlService
            ->expects(self::once())
            ->method('findUsages')
            ->with($url, 0, 0)
            ->willReturn($searchResults);

        $adapter = new URLUsagesAdapter($url, $this->urlService);

        self::assertEquals(
            $searchResults->totalCount,
            $adapter->getNbResults()
        );
    }

    public function testGetSlice(): void
    {
        $url = $this->createMock(URL::class);
        $offset = 10;
        $limit = 25;

        $searchResults = new UsageSearchResult([
            'items' => [
                $this->createMock(SearchHit::class),
                $this->createMock(SearchHit::class),
                $this->createMock(SearchHit::class),
            ],
            'totalCount' => 13,
        ]);

        $this->urlService
            ->expects(self::once())
            ->method('findUsages')
            ->with($url, $offset, $limit)
            ->willReturn($searchResults);

        $adapter = new URLUsagesAdapter($url, $this->urlService);

        self::assertEquals(
            $searchResults->items,
            $adapter->getSlice($offset, $limit)
        );
    }
}
