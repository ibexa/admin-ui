<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Pagination\Pagerfanta;

use Ibexa\AdminUi\Pagination\Pagerfanta\ContentTypeListAdapter;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\ContentTypeQuery;
use Ibexa\Contracts\Core\Repository\Values\ContentType\SearchResult;
use PHPUnit\Framework\TestCase;

final class ContentTypeListAdapterTest extends TestCase
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService&\PHPUnit\Framework\MockObject\MockObject */
    private ContentTypeService $contentTypeService;

    protected function setUp(): void
    {
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
    }

    public function testGetNbResults(): void
    {
        $languages = ['en-GB', 'de-DE'];

        $searchResults = new SearchResult([
            'totalCount' => 10,
            'items' => [],
        ]);

        $this->contentTypeService
            ->expects(self::once())
            ->method('findContentTypes')
            ->with($this->isInstanceOf(ContentTypeQuery::class), $languages)
            ->willReturn($searchResults);

        $adapter = new ContentTypeListAdapter($this->contentTypeService, $languages);

        self::assertEquals(
            10,
            $adapter->getNbResults()
        );
    }

    public function testGetSlice(): void
    {
        $languages = ['en-GB'];
        $offset = 5;
        $limit = 25;

        $item1 = $this->createMock(ContentType::class);
        $item2 = $this->createMock(ContentType::class);
        $items = [$item1, $item2];

        $searchResults = new SearchResult([
            'totalCount' => 2,
            'items' => $items,
        ]);

        $this->contentTypeService
            ->expects(self::once())
            ->method('findContentTypes')
            ->with($this->isInstanceOf(ContentTypeQuery::class), $languages)
            ->willReturn($searchResults);

        $adapter = new ContentTypeListAdapter($this->contentTypeService, $languages);

        self::assertEquals(
            $searchResults->getIterator(),
            $adapter->getSlice($offset, $limit)
        );
    }
}
