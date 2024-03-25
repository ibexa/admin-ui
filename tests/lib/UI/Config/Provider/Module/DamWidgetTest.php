<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\UI\Config\Provider\Module;

use Ibexa\AdminUi\UI\Config\Provider\Module\DamWidget;
use Ibexa\Bundle\Core\ApiLoader\Exception\InvalidSearchEngine;
use Ibexa\Bundle\Core\ApiLoader\RepositoryConfigurationProvider;
use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use PHPUnit\Framework\TestCase;

/**
 * @template TDamWidgetConfig of array {
 *     image: array{
 *         fieldDefinitionIdentifiers: array<string>,
 *         contentTypeIdentifiers: array<string>,
 *         aggregations: aggregations: array<string, array<string, string>>,
 *         showImageFilters: bool,
 *     },
 *     contentTypeIdentifier: string,
 *     nameFieldIdentifier: string,
 * }
 * @template TRepositoryConfig of array {
 *      engine: string,
 *      connection: string,
 *      search: array{
 *          engine: string,
 *      },
 *  }
 *
 * @covers \Ibexa\AdminUi\UI\Config\Provider\Module\ImagePicker
 */
final class DamWidgetTest extends TestCase
{
    private const IMAGE_FIELD_DEFINITION_IDENTIFIERS = ['field_foo', 'field_bar'];
    private const IMAGE_CONTENT_TYPE_IDENTIFIERS = ['content_type_foo', 'content_type_bar'];
    private const IMAGE_AGGREGATIONS = [
        'KeywordTermAggregation' => [
            'name' => 'keywords',
            'contentTypeIdentifier' => 'keywords',
            'fieldDefinitionIdentifier' => 'keywords',
        ],
    ];

    private const FOLDER_CONTENT_TYPE_IDENTIFIER = 'folder';
    private const FOLDER_NAME_FIELD_IDENTIFIER = 'name';

    private ProviderInterface $provider;

    /** @var \Ibexa\Bundle\Core\ApiLoader\RepositoryConfigurationProvider&\PHPUnit\Framework\MockObject\MockObject */
    private RepositoryConfigurationProvider $repositoryConfigurationProvider;

    protected function setUp(): void
    {
        $this->repositoryConfigurationProvider = $this->createMock(RepositoryConfigurationProvider::class);
        $this->provider = new DamWidget(
            [
                'image' => [
                    'fieldDefinitionIdentifiers' => self::IMAGE_FIELD_DEFINITION_IDENTIFIERS,
                    'contentTypeIdentifiers' => self::IMAGE_CONTENT_TYPE_IDENTIFIERS,
                    'aggregations' => self::IMAGE_AGGREGATIONS,
                ],
                'folder' => [
                    'contentTypeIdentifier' => self::FOLDER_CONTENT_TYPE_IDENTIFIER,
                    'nameFieldIdentifier' => self::FOLDER_NAME_FIELD_IDENTIFIER,
                ],
            ],
            $this->repositoryConfigurationProvider
        );
    }

    /**
     * @dataProvider provideDataForTestGetConfig
     *
     * @phpstan-param TDamWidgetConfig $expectedConfiguration
     * @phpstan-param TRepositoryConfig $repositoryConfig
     */
    public function testGetConfig(
        array $expectedConfiguration,
        array $repositoryConfig
    ): void {
        $this->mockRepositoryConfigurationProviderGetRepositoryConfig($repositoryConfig);

        self::assertSame(
            $expectedConfiguration,
            $this->provider->getConfig()
        );
    }

    public function testGetConfigThrowInvalidSearchEngine(): void
    {
        $repositoryAlias = 'foo';
        $this->mockRepositoryConfigurationProviderGetRepositoryConfig(
            ['alias' => $repositoryAlias]
        );
        $this->mockRepositoryConfigurationProviderGetCurrentRepositoryAlias($repositoryAlias);

        $this->expectException(InvalidSearchEngine::class);
        $this->expectExceptionMessage('Ibexa "foo" Repository has no Search Engine configured');

        $this->provider->getConfig();
    }

    /**
     * @return iterable<array{
     *     TDamWidgetConfig,
     *     TRepositoryConfig
     * }>
     */
    public function provideDataForTestGetConfig(): iterable
    {
        yield 'Legacy Search Engine - hide filters' => [
            $this->getExpectedConfig(false),
            $this->getRepositoryConfig('legacy'),
        ];

        $expectedConfigForSolrAndElasticsearch = $this->getExpectedConfig(true);

        yield 'Solr - show filters' => [
            $expectedConfigForSolrAndElasticsearch,
            $this->getRepositoryConfig('solr'),
        ];

        yield 'Elasticsearch - show filters' => [
            $expectedConfigForSolrAndElasticsearch,
            $this->getRepositoryConfig('elasticsearch'),
        ];
    }

    /**
     * @phpstan-return TDamWidgetConfig
     */
    private function getExpectedConfig(bool $showImageFilters): array
    {
        return [
            'image' => [
                'fieldDefinitionIdentifiers' => self::IMAGE_FIELD_DEFINITION_IDENTIFIERS,
                'contentTypeIdentifiers' => self::IMAGE_CONTENT_TYPE_IDENTIFIERS,
                'aggregations' => self::IMAGE_AGGREGATIONS,
                'showImageFilters' => $showImageFilters,
            ],
            'folder' => [
                'contentTypeIdentifier' => self::FOLDER_CONTENT_TYPE_IDENTIFIER,
                'nameFieldIdentifier' => self::FOLDER_NAME_FIELD_IDENTIFIER,
            ],
        ];
    }

    /**
     * @phpstan-return TRepositoryConfig
     */
    private function getRepositoryConfig(string $searchEngine): array
    {
        return [
            'engine' => 'foo',
            'connection' => 'some_connection',
            'search' => [
                'engine' => $searchEngine,
            ],
        ];
    }

    /**
     * @param array<string, string|array<string>> $config
     */
    private function mockRepositoryConfigurationProviderGetRepositoryConfig(array $config): void
    {
        $this->repositoryConfigurationProvider
            ->method('getRepositoryConfig')
            ->willReturn($config);
    }

    private function mockRepositoryConfigurationProviderGetCurrentRepositoryAlias(string $repositoryAlias): void
    {
        $this->repositoryConfigurationProvider
            ->method('getCurrentRepositoryAlias')
            ->willReturn($repositoryAlias);
    }
}
