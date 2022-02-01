<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\AdminUi\Pagination\Pagerfanta;

use Ibexa\AdminUi\Pagination\Pagerfanta\URLWildcardAdapter;
use Ibexa\Contracts\Core\Repository\URLWildcardService;
use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard;
use PHPUnit\Framework\TestCase;

final class URLWildcardAdapterTest extends TestCase
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard|\PHPUnit\Framework\MockObject\MockObject */
    private $urlWildcardService;

    protected function setUp(): void
    {
        $this->urlWildcardService = $this->createMock(URLWildcardService::class);
    }

    public function testGetNbResults(): void
    {
        $countAll = 5;

        $this->urlWildcardService
            ->expects($this->once())
            ->method('countAll')
            ->willReturn($countAll);

        $adapter = new URLWildcardAdapter($this->urlWildcardService);

        $this->assertEquals(
            $countAll,
            $adapter->getNbResults()
        );

        $adapter->getNbResults();
    }

    public function testGetSlice(): void
    {
        $offset = 10;
        $limit = 25;

        $expectedResult = $this->urlWildcards();

        $this->urlWildcardService
            ->expects($this->once())
            ->method('loadAll')
            ->with($offset, $limit)
            ->willReturn($expectedResult);

        $adapter = new URLWildcardAdapter($this->urlWildcardService);

        $this->assertEquals(
            $expectedResult,
            $adapter->getSlice($offset, $limit)
        );
    }

    /**
     * @return  \Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard[]
     */
    public function urlWildcards(): array
    {
        return [
            new URLWildcard([
                'id' => 1,
                'destinationUrl' => 'test',
                'sourceUrl' => '/',
                'forward' => true,
            ]),

            new URLWildcard([
                'id' => 2,
                'destinationUrl' => 'test2',
                'sourceUrl' => '/test',
                'forward' => false,
            ]),
        ];
    }
}

class_alias(URLWildcardAdapterTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Pagination\Pagerfanta\URLWildcardAdapterTest');
