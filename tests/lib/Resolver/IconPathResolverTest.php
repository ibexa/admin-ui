<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Resolver;

use Ibexa\AdminUi\Resolver\IconPathResolver;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;

final class IconPathResolverTest extends TestCase
{
    private ConfigResolverInterface&MockObject $configResolver;

    private Packages&MockObject $packages;

    public function setUp(): void
    {
        $config = $this->getDefaultConfig();

        $this->configResolver = $this->getConfigResolverMock($config);
        $this->packages = $this->getPackagesMock($config);
    }

    /**
     * @dataProvider resolveDataProvider
     */
    public function testResolve(string $icon, ?string $set, string $expectedPath): void
    {
        $iconPathResolver = new IconPathResolver($this->configResolver, $this->packages);

        self::assertEquals($expectedPath, $iconPathResolver->resolve($icon, $set));
    }

    /**
     * @return array<array{string, ?string, string}>
     */
    public function resolveDataProvider(): array
    {
        return [
            [
                'bookmark',
                'my_icon_set',
                '/bundles/mybundle/my-icons.svg#bookmark',
            ],
            [
                'folder',
                null,
                '/bundles/mybundle/my-icons.svg#folder',
            ],
            [
                'bookmark',
                'my_other_icon_set',
                '/bundles/my_other_icon_set/my-other-icons.svg#bookmark',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getDefaultConfig(): array
    {
        return [
            'icon_sets' => [
                'my_icon_set' => '/bundles/mybundle/my-icons.svg',
                'my_other_icon_set' => '/bundles/my_other_icon_set/my-other-icons.svg',
            ],
            'default_icon_set' => 'my_icon_set',
        ];
    }

    /**
     * @param array<string, mixed> $config
     */
    private function getConfigResolverMock(array $config): ConfigResolverInterface&MockObject
    {
        $configResolver = $this->createMock(ConfigResolverInterface::class);
        $configResolver->method('getParameter')->willReturnMap([
            ['assets.icon_sets', null, null, $config['icon_sets']],
            ['assets.default_icon_set', null, null, $config['default_icon_set']],
        ]);

        return $configResolver;
    }

    /**
     * @param array<string, mixed> $config
     */
    private function getPackagesMock(array $config): Packages&MockObject
    {
        $packages = $this->createMock(Packages::class);
        $packages->method('getUrl')->willReturnMap([
            [$config['icon_sets']['my_icon_set'], null, $config['icon_sets']['my_icon_set']],
            [$config['icon_sets']['my_other_icon_set'], null, $config['icon_sets']['my_other_icon_set']],
        ]);

        return $packages;
    }
}
