<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Headless;

use Ibexa\AdminUi\Headless\HeadlessConfiguration;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(HeadlessConfiguration::class)]
final class HeadlessConfigurationTest extends TestCase
{
    /**
     * @return iterable<string, array{mixed, bool}>
     */
    public static function dataProviderForTestIsEnabled(): iterable
    {
        yield 'true' => [true, true];
        yield 'false' => [false, false];
        yield 'null' => [null, false];
    }

    #[DataProvider('dataProviderForTestIsEnabled')]
    public function testIsEnabled(mixed $parameter, bool $expected): void
    {
        $configuration = new HeadlessConfiguration(
            $this->createConfigResolver(['headless.enabled' => $parameter], 'site')
        );

        self::assertSame($expected, $configuration->isEnabled('site'));
    }

    /**
     * @return iterable<string, array{mixed, string|null}>
     */
    public static function dataProviderForTestUrlGetters(): iterable
    {
        yield 'url' => ['https://frontend.example.com/', 'https://frontend.example.com/'];
        yield 'empty string' => ['', null];
        yield 'null' => [null, null];
        yield 'not a string' => [false, null];
    }

    #[DataProvider('dataProviderForTestUrlGetters')]
    public function testUrlGetters(mixed $parameter, ?string $expected): void
    {
        $configuration = new HeadlessConfiguration(
            $this->createConfigResolver([
                'headless.page_builder.preview_url' => $parameter,
                'headless.content.preview_url' => $parameter,
                'headless.configuration_url' => $parameter,
            ])
        );

        self::assertSame($expected, $configuration->getPageBuilderPreviewUrl());
        self::assertSame($expected, $configuration->getContentPreviewUrl());
        self::assertSame($expected, $configuration->getConfigurationUrl());
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function createConfigResolver(array $parameters, ?string $expectedScope = null): ConfigResolverInterface
    {
        $configResolver = $this->createMock(ConfigResolverInterface::class);
        $configResolver
            ->method('getParameter')
            ->willReturnCallback(
                static function (string $name, ?string $namespace, ?string $scope) use ($parameters, $expectedScope): mixed {
                    self::assertNull($namespace);
                    self::assertSame($expectedScope, $scope);
                    self::assertArrayHasKey($name, $parameters);

                    return $parameters[$name];
                }
            );

        return $configResolver;
    }
}
