<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\UI\Config\Service;

use Ibexa\AdminUi\UI\Service\ContentTypeIconResolver;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;

class ContentTypeIconResolverTest extends TestCase
{
    private ConfigResolverInterface&MockObject $configResolver;

    private Packages&MockObject $packages;

    private ContentTypeIconResolver $contentTypeIconResolver;

    protected function setUp(): void
    {
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->packages = $this->createMock(Packages::class);

        $this->contentTypeIconResolver = new ContentTypeIconResolver(
            $this->configResolver,
            $this->packages
        );
    }

    /**
     * @dataProvider dataProviderForGetContentTypeIcon
     */
    public function testGetContentTypeIcon(array $config, string $identifier, string $expected): void
    {
        $this->configResolver
            ->expects(self::any())
            ->method('hasParameter')
            ->willReturnCallback(static function (string $key) use ($config): bool {
                $key = explode('.', $key);

                return isset($config[array_pop($key)]);
            });

        $this->configResolver
            ->expects(self::any())
            ->method('getParameter')
            ->willReturnCallback(static function (string $key) use ($config) {
                $key = explode('.', $key);

                return $config[array_pop($key)];
            });

        $this->packages
            ->expects(self::any())
            ->method('getUrl')
            ->willReturnCallback(static function (string $uri): string {
                return "https://cdn.example.com/$uri";
            });

        self::assertEquals($expected, $this->contentTypeIconResolver->getContentTypeIcon($identifier));
    }

    public function dataProviderForGetContentTypeIcon(): array
    {
        return [
            [
                [
                    'custom' => [
                        'thumbnail' => 'icon.svg#custom',
                    ],
                    'default-config' => [
                        'thumbnail' => 'icon.svg#default',
                    ],
                ],
                'custom',
                'https://cdn.example.com/icon.svg#custom',
            ],
            [
                [
                    'custom-without-fragment' => [
                        'thumbnail' => 'icon.png',
                    ],
                    'default-config' => [
                        'thumbnail' => 'icon.svg#default',
                    ],
                ],
                'custom-without-fragment',
                'https://cdn.example.com/icon.png',
            ],
            [
                [
                    'custom-without-icon' => [
                        'thumbnail' => null,
                    ],
                    'default-config' => [
                        'thumbnail' => 'icon.svg#default',
                    ],
                ],
                'custom-without-icon',
                'https://cdn.example.com/icon.svg#default',
            ],
            [
                [
                    'default-config' => [
                        'thumbnail' => 'icon.svg#default',
                    ],
                ],
                'custom-with-missing-config',
                'https://cdn.example.com/icon.svg#default',
            ],
        ];
    }
}
