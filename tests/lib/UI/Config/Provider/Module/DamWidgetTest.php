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
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\NameSchema\SchemaIdentifierExtractorInterface;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-import-type TImageConfig from DamWidget
 * @phpstan-import-type TFolderConfig from DamWidget
 *
 * @phpstan-type TDamWidgetConfig array{
 *     image: TImageConfig,
 *     folder: TFolderConfig
 * }
 * @phpstan-type TRepositoryConfig array{
 *     engine: string,
 *     connection: string,
 *     search: array{
 *         engine: string,
 *     },
 * }
 * @phpstan-type TContentTypeValueMap array<
 *     array{
 *         string,
 *         array{},
 *         \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType
 *     }
 * >
 * @phpstan-type TSchemaIdentifiersValueMap array<
 *     array{
 *         string,
 *         array{field: array<string>}
 *     }
 * >
 *
 * @covers \Ibexa\AdminUi\UI\Config\Provider\Module\ImagePicker
 */
final class DamWidgetTest extends TestCase
{
    private const IMAGE_FOO_CONTENT_TYPE_IDENTIFIER = 'content_type_foo';
    private const IMAGE_BAR_CONTENT_TYPE_IDENTIFIER = 'content_type_bar';
    private const IMAGE_FOO_NAME_SCHEMA = '<image_title|name>';
    private const IMAGE_BAR_NAME_SCHEMA = '<caption|name>';
    private const IMAGE_FOO_NAME_SCHEMA_IDENTIFIERS = ['image_title', 'name'];
    private const IMAGE_BAR_NAME_SCHEMA_IDENTIFIERS = ['name'];
    private const IMAGE_MAPPINGS = [
        self::IMAGE_FOO_CONTENT_TYPE_IDENTIFIER => [
            'imageFieldIdentifier' => 'field_foo',
            'nameSchemaIdentifiers' => self:: IMAGE_FOO_NAME_SCHEMA_IDENTIFIERS,
        ],
        self::IMAGE_BAR_CONTENT_TYPE_IDENTIFIER => [
            'imageFieldIdentifier' => 'field_bar',
            'nameSchemaIdentifiers' => self:: IMAGE_BAR_NAME_SCHEMA_IDENTIFIERS,
        ],
    ];
    private const IMAGE_FIELD_DEFINITION_IDENTIFIERS = ['field_foo', 'field_bar'];
    private const IMAGE_AGGREGATIONS = [
        'KeywordTermAggregation' => [
            'name' => 'keywords',
            'contentTypeIdentifier' => 'keywords',
            'fieldDefinitionIdentifier' => 'keywords',
        ],
    ];

    private const FOLDER_CONTENT_TYPE_IDENTIFIER = 'folder';
    private const FOLDER_NAME_SCHEMA = '<short_name|name>';
    private const FOLDER_NAME_SCHEMA_IDENTIFIERS = ['short_name', 'name'];

    private ProviderInterface $provider;

    /** @var \Ibexa\Bundle\Core\ApiLoader\RepositoryConfigurationProvider&\PHPUnit\Framework\MockObject\MockObject */
    private RepositoryConfigurationProvider $repositoryConfigurationProvider;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService&\PHPUnit\Framework\MockObject\MockObject */
    private ContentTypeService $contentTypeService;

    /** @var \Ibexa\Contracts\Core\Repository\NameSchema\SchemaIdentifierExtractorInterface&\PHPUnit\Framework\MockObject\MockObject */
    private SchemaIdentifierExtractorInterface $schemaIdentifierExtractor;

    protected function setUp(): void
    {
        $this->repositoryConfigurationProvider = $this->createMock(RepositoryConfigurationProvider::class);
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
        $this->schemaIdentifierExtractor = $this->createMock(SchemaIdentifierExtractorInterface::class);

        $this->provider = new DamWidget(
            [
                'image' => [
                    'mappings' => [
                        self::IMAGE_FOO_CONTENT_TYPE_IDENTIFIER => [
                            'imageFieldIdentifier' => 'field_foo',
                        ],
                        self::IMAGE_BAR_CONTENT_TYPE_IDENTIFIER => [
                            'imageFieldIdentifier' => 'field_bar',
                        ],
                    ],
                    'aggregations' => self::IMAGE_AGGREGATIONS,
                ],
                'folder' => [
                    'contentTypeIdentifier' => self::FOLDER_CONTENT_TYPE_IDENTIFIER,
                ],
            ],
            $this->contentTypeService,
            $this->repositoryConfigurationProvider,
            $this->schemaIdentifierExtractor
        );
    }

    /**
     * @dataProvider provideDataForTestGetConfig
     *
     * @phpstan-param TDamWidgetConfig $expectedConfiguration
     * @phpstan-param TRepositoryConfig $repositoryConfig
     *
     * @param TContentTypeValueMap $loadContentTypeValueMap
     * @param TSchemaIdentifiersValueMap $extractSchemaIdentifiersValueMap
     */
    public function testGetConfig(
        array $expectedConfiguration,
        array $repositoryConfig,
        array $loadContentTypeValueMap,
        array $extractSchemaIdentifiersValueMap
    ): void {
        $this->mockRepositoryConfigurationProviderGetRepositoryConfig($repositoryConfig);
        $this->mockContentTypeServiceLoadContentTypeByIdentifier($loadContentTypeValueMap);
        $this->mockSchemaIdentifierExtractorExtract($extractSchemaIdentifiersValueMap);

        self::assertEquals(
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
     *     TRepositoryConfig,
     *     TContentTypeValueMap,
     *     TSchemaIdentifiersValueMap,
     * }>
     */
    public function provideDataForTestGetConfig(): iterable
    {
        $loadContentTypeValueMap = [
            [self::FOLDER_CONTENT_TYPE_IDENTIFIER, [], $this->createContentTypeMock(self::FOLDER_NAME_SCHEMA)],
            [self::IMAGE_FOO_CONTENT_TYPE_IDENTIFIER, [], $this->createContentTypeMock(self::IMAGE_FOO_NAME_SCHEMA)],
            [self::IMAGE_BAR_CONTENT_TYPE_IDENTIFIER, [], $this->createContentTypeMock(self::IMAGE_BAR_NAME_SCHEMA)],
        ];

        $extractSchemaIdentifiersValueMap = [
            [self::FOLDER_NAME_SCHEMA, ['field' => self::FOLDER_NAME_SCHEMA_IDENTIFIERS]],
            [self::IMAGE_FOO_NAME_SCHEMA, ['field' => self::IMAGE_FOO_NAME_SCHEMA_IDENTIFIERS]],
            [self::IMAGE_BAR_NAME_SCHEMA, ['field' => self::IMAGE_BAR_NAME_SCHEMA_IDENTIFIERS]],
        ];

        yield 'Legacy Search Engine - hide filters' => [
            $this->getExpectedConfig(false),
            $this->getRepositoryConfig('legacy'),
            $loadContentTypeValueMap,
            $extractSchemaIdentifiersValueMap,
        ];

        $expectedConfigForSolrAndElasticsearch = $this->getExpectedConfig(true);

        yield 'Solr - show filters' => [
            $expectedConfigForSolrAndElasticsearch,
            $this->getRepositoryConfig('solr'),
            $loadContentTypeValueMap,
            $extractSchemaIdentifiersValueMap,
        ];

        yield 'Elasticsearch - show filters' => [
            $expectedConfigForSolrAndElasticsearch,
            $this->getRepositoryConfig('elasticsearch'),
            $loadContentTypeValueMap,
            $extractSchemaIdentifiersValueMap,
        ];
    }

    private function createContentTypeMock(string $nameSchema): ContentType
    {
        $contentType = $this->createMock(ContentType::class);
        $contentType
            ->method('__get')
            ->with('nameSchema')
            ->willReturn($nameSchema);

        return $contentType;
    }

    /**
     * @phpstan-return TDamWidgetConfig
     */
    private function getExpectedConfig(bool $showImageFilters): array
    {
        return [
            'image' => [
                'fieldDefinitionIdentifiers' => self::IMAGE_FIELD_DEFINITION_IDENTIFIERS,
                'contentTypeIdentifiers' => [
                    self::IMAGE_FOO_CONTENT_TYPE_IDENTIFIER,
                    self::IMAGE_BAR_CONTENT_TYPE_IDENTIFIER,
                ],
                'aggregations' => self::IMAGE_AGGREGATIONS,
                'showImageFilters' => $showImageFilters,
                'mappings' => self::IMAGE_MAPPINGS,
            ],
            'folder' => [
                'contentTypeIdentifier' => self::FOLDER_CONTENT_TYPE_IDENTIFIER,
                'nameSchemaIdentifiers' => self::FOLDER_NAME_SCHEMA_IDENTIFIERS,
            ],
            'folder' => [
                'contentTypeIdentifier' => self::FOLDER_CONTENT_TYPE_IDENTIFIER,
                'nameSchemaIdentifiers' => self::FOLDER_NAME_SCHEMA_IDENTIFIERS,
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

    private function mockContentTypeServiceLoadContentTypeByIdentifier(
        string $contentTypeIdentifier,
        ContentType $contentType
    ): void {
        $this->contentTypeService
            ->method('loadContentTypeByIdentifier')
            ->with($contentTypeIdentifier)
            ->willReturn($contentType);
    }

    /**
     * @param array{
     *     field: array<string>
     * } $nameSchemaIdentifiers
     */
    private function mockSchemaIdentifierExtractorExtract(
        string $nameSchema,
        array $nameSchemaIdentifiers
    ): void {
        $this->schemaIdentifierExtractor
            ->method('extract')
            ->with($nameSchema)
            ->willReturn($nameSchemaIdentifiers);
    }

    /**
     * @param array<array<string|array<string>|ContentType>> $valueMap
     */
    private function mockContentTypeServiceLoadContentTypeByIdentifier(array $valueMap): void
    {
        $this->contentTypeService
            ->method('loadContentTypeByIdentifier')
            ->willReturnMap($valueMap);
    }

    /**
     * @param array<array{string|array<string>}> $valueMap
     */
    private function mockSchemaIdentifierExtractorExtract(array $valueMap): void
    {
        $this->schemaIdentifierExtractor
            ->method('extract')
            ->willReturnMap($valueMap);
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
