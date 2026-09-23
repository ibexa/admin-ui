<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\DependencyInjection\Configuration\Parser;

use Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser\Headless;
use Ibexa\Bundle\Core\DependencyInjection\IbexaCoreExtension;
use Ibexa\Tests\Bundle\Core\DependencyInjection\Configuration\Parser\AbstractParserTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Headless::class)]
final class HeadlessTest extends AbstractParserTestCase
{
    private const string GROUP = 'ibexa_demo_group';
    private const string SITE_ACCESS = 'ibexa_demo_site';
    private const string SITE_ACCESS_PARAMETER_PREFIX = 'ibexa.site_access.config.ibexa_demo_site.';
    private const string PAGE_BUILDER_PREVIEW_URL = 'https://frontend.example.com/page-builder';
    private const string CONTENT_PREVIEW_URL = 'https://frontend.example.com/preview';
    private const string CONFIGURATION_URL = 'https://admin.example.com/frontend';

    protected function getContainerExtensions(): array
    {
        return [
            new IbexaCoreExtension([new Headless()]),
        ];
    }

    /**
     * @param array<string, mixed> $headless
     * @param array<string, mixed> $expectedParameters
     * @param string[] $expectedMissingParameters
     */
    #[DataProvider('dataProviderForTestSettings')]
    public function testSettings(array $headless, array $expectedParameters, array $expectedMissingParameters): void
    {
        $this->load([
            'system' => [
                self::SITE_ACCESS => [
                    'headless' => $headless,
                ],
            ],
        ]);

        foreach ($expectedParameters as $name => $value) {
            self::assertTrue($this->container->hasParameter(self::SITE_ACCESS_PARAMETER_PREFIX . $name));
            $this->assertConfigResolverParameterValue($name, $value, self::SITE_ACCESS);
        }

        foreach ($expectedMissingParameters as $name) {
            self::assertFalse($this->container->hasParameter(self::SITE_ACCESS_PARAMETER_PREFIX . $name));
        }
    }

    /**
     * @return iterable<string, array{array<string, mixed>, array<string, mixed>, string[]}>
     */
    public static function dataProviderForTestSettings(): iterable
    {
        yield 'full configuration' => [
            [
                'enabled' => true,
                'page_builder' => ['preview_url' => self::PAGE_BUILDER_PREVIEW_URL],
                'content' => ['preview_url' => self::CONTENT_PREVIEW_URL],
                'configuration_url' => self::CONFIGURATION_URL,
            ],
            [
                'headless.enabled' => true,
                'headless.page_builder.preview_url' => self::PAGE_BUILDER_PREVIEW_URL,
                'headless.content.preview_url' => self::CONTENT_PREVIEW_URL,
                'headless.configuration_url' => self::CONFIGURATION_URL,
            ],
            [],
        ];

        yield 'only the flag' => [
            ['enabled' => false],
            ['headless.enabled' => false],
            [
                'headless.page_builder.preview_url',
                'headless.content.preview_url',
                'headless.configuration_url',
            ],
        ];

        yield 'explicit nulls are mapped as nulls' => [
            [
                'page_builder' => ['preview_url' => null],
                'configuration_url' => null,
            ],
            [
                'headless.page_builder.preview_url' => null,
                'headless.configuration_url' => null,
            ],
            [
                'headless.enabled',
                'headless.content.preview_url',
            ],
        ];

        yield 'empty nested node maps nothing' => [
            ['page_builder' => []],
            [],
            [
                'headless.enabled',
                'headless.page_builder.preview_url',
                'headless.content.preview_url',
                'headless.configuration_url',
            ],
        ];
    }

    public function testSiteAccessInheritsGroupSettings(): void
    {
        $this->load([
            'system' => [
                self::GROUP => [
                    'headless' => [
                        'enabled' => true,
                        'page_builder' => ['preview_url' => self::PAGE_BUILDER_PREVIEW_URL],
                        'content' => ['preview_url' => self::CONTENT_PREVIEW_URL],
                        'configuration_url' => self::CONFIGURATION_URL,
                    ],
                ],
                // A siteaccess block without the "headless" node must not shadow the group values
                self::SITE_ACCESS => [],
            ],
        ]);

        self::assertFalse($this->container->hasParameter(self::SITE_ACCESS_PARAMETER_PREFIX . 'headless.enabled'));
        $this->assertConfigResolverParameterValue('headless.enabled', true, self::SITE_ACCESS);
        $this->assertConfigResolverParameterValue(
            'headless.page_builder.preview_url',
            self::PAGE_BUILDER_PREVIEW_URL,
            self::SITE_ACCESS
        );
        $this->assertConfigResolverParameterValue(
            'headless.content.preview_url',
            self::CONTENT_PREVIEW_URL,
            self::SITE_ACCESS
        );
        $this->assertConfigResolverParameterValue(
            'headless.configuration_url',
            self::CONFIGURATION_URL,
            self::SITE_ACCESS
        );
    }

    public function testEmptyHeadlessNodeOnSiteAccessInheritsGroupSettings(): void
    {
        $this->load([
            'system' => [
                self::GROUP => [
                    'headless' => ['enabled' => true],
                ],
                // "headless: ~" is normalized by Symfony to an empty node and must map nothing
                self::SITE_ACCESS => [
                    'headless' => null,
                ],
            ],
        ]);

        self::assertFalse($this->container->hasParameter(self::SITE_ACCESS_PARAMETER_PREFIX . 'headless.enabled'));
        $this->assertConfigResolverParameterValue('headless.enabled', true, self::SITE_ACCESS);
    }

    public function testSiteAccessOverridesOnlyConfiguredKeys(): void
    {
        $this->load([
            'system' => [
                self::GROUP => [
                    'headless' => [
                        'enabled' => true,
                        'page_builder' => ['preview_url' => self::PAGE_BUILDER_PREVIEW_URL],
                        'configuration_url' => self::CONFIGURATION_URL,
                    ],
                ],
                self::SITE_ACCESS => [
                    'headless' => [
                        'enabled' => false,
                        'configuration_url' => null,
                    ],
                ],
            ],
        ]);

        $this->assertConfigResolverParameterValue('headless.enabled', false, self::SITE_ACCESS);
        $this->assertConfigResolverParameterValue('headless.configuration_url', null, self::SITE_ACCESS);
        $this->assertConfigResolverParameterValue(
            'headless.page_builder.preview_url',
            self::PAGE_BUILDER_PREVIEW_URL,
            self::SITE_ACCESS
        );
    }
}
