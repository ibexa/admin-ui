<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Strategy\FocusMode;

use Ibexa\AdminUi\Strategy\FocusMode\ContentStructureRedirectStrategy;
use Ibexa\Contracts\AdminUi\FocusMode\RedirectStrategyInterface;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

final class ContentStructureRedirectStrategyTest extends TestCase
{
    private RedirectStrategyInterface $strategy;

    protected function setUp(): void
    {
        $this->strategy = new ContentStructureRedirectStrategy(
            $this->createConfigResolverMock(),
            $this->createLocationServiceMock(),
            $this->createRouterMock()
        );
    }

    /**
     * @dataProvider dataProviderForTestSupports
     *
     * @param array<string, string> $pathData
     */
    public function testSupports(array $pathData, bool $doesSupport): void
    {
        self::assertSame($doesSupport, $this->strategy->supports($pathData));
    }

    /**
     * @return iterable<array{array<string, string>, bool}>
     */
    public function dataProviderForTestSupports(): iterable
    {
        yield 'foo.path' => [['_route' => 'foo.path'], false];

        yield 'ibexa.content.view' => [['_route' => 'ibexa.content.view'], false];

        yield 'ibexa.section.list' => [['_route' => 'ibexa.section.list'], true];

        yield 'ibexa.content_type_group.list' => [['_route' => 'ibexa.content_type_group.list'], true];

        yield 'ibexa.object_state.groups.list' => [['_route' => 'ibexa.object_state.groups.list'], true];

        yield 'ibexa.content_type_group.view' => [['_route' => 'ibexa.content_type_group.view'], true];
    }

    /**
     * @dataProvider dataProviderForTestGenerateRedirectPath
     */
    public function testGenerateRedirectPath(string $path, string $expectedPath): void
    {
        self::assertSame(
            $expectedPath,
            $this->strategy->generateRedirectPath($path)
        );
    }

    /**
     * @return iterable<array{string, string}>
     */
    public function dataProviderForTestGenerateRedirectPath(): iterable
    {
        yield 'ibexa.section.list' => ['ibexa.section.list', '/admin/view/content/456/full/1/123'];

        yield 'ibexa.content_type_group.list' => ['ibexa.content_type_group.list', '/admin/view/content/456/full/1/123'];
    }

    /**
     * @return \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createConfigResolverMock(): ConfigResolverInterface
    {
        $configResolverMock = $this->createMock(ConfigResolverInterface::class);
        $configResolverMock
            ->method('getParameter')
            ->with('location_ids.content_structure')
            ->willReturn($this->getLocation()->id);

        return $configResolverMock;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\LocationService&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createLocationServiceMock(): LocationService
    {
        $location = $this->getLocation();

        $locationServiceMock = $this->createMock(LocationService::class);
        $locationServiceMock->method('loadLocation')->willReturn($location);

        return $locationServiceMock;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createRouterMock(): RouterInterface
    {
        $location = $this->getLocation();
        $routerMock = $this->createMock(RouterInterface::class);

        $routerMock
            ->method('generate')
            ->with(
                'ibexa.content.view',
                [
                    'locationId' => $location->id,
                    'contentId' => $location->getContentInfo()->id,
                ]
            )
            ->willReturn('/admin/view/content/456/full/1/123');

        return $routerMock;
    }

    private function getLocation(): Location
    {
        return new Location([
            'id' => 123,
            'contentInfo' => new ContentInfo([
                'id' => 456,
            ]),
        ]);
    }
}
