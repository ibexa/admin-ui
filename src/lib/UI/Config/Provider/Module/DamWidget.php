<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider\Module;

use Ibexa\Bundle\Core\ApiLoader\Exception\InvalidSearchEngine;
use Ibexa\Bundle\Core\ApiLoader\RepositoryConfigurationProvider;
use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\NameSchema\SchemaIdentifierExtractorInterface;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

/**
 * @phpstan-type TConfig array{
 *     image: array{
 *         aggregations: array<string, array<string, string>>,
 *         mappings: array<
 *             string,
 *             array{
 *                 imageFieldIdentifier: string
 *             },
 *         >,
 *     },
 *     folder: array{
 *         contentTypeIdentifier: string,
 *     }
 * }
 * @phpstan-type TImageConfig array{
 *     fieldDefinitionIdentifiers: array<string>,
 *     contentTypeIdentifiers: array<string>,
 *     aggregations: array<string, array<string, string>>,
 *     showImageFilters: bool,
 *     enableMultipleDownload: bool,
 *     mappings: array<
 *         string,
 *         array{
 *             imageFieldIdentifier: string,
 *             nameSchemaIdentifiers: array<string>,
 *         }
 *     >,
 * }
 * @phpstan-type TFolderConfig array{
 *     contentTypeIdentifier: string,
 *     nameSchemaIdentifiers: array<string>
 * }
 */
final class DamWidget implements ProviderInterface
{
    /** @phpstan-var TConfig */
    private array $config;

    private ContentTypeService $contentTypeService;

    private RepositoryConfigurationProvider $repositoryConfigurationProvider;

    private SchemaIdentifierExtractorInterface $schemaIdentifierExtractor;

    /**
     * @phpstan-param TConfig $config
     */
    public function __construct(
        array $config,
        ContentTypeService $contentTypeService,
        RepositoryConfigurationProvider $repositoryConfigurationProvider,
        SchemaIdentifierExtractorInterface $schemaIdentifierExtractor
    ) {
        $this->config = $config;
        $this->contentTypeService = $contentTypeService;
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
        $this->schemaIdentifierExtractor = $schemaIdentifierExtractor;
    }

    /**
     * @phpstan-return array{
     *     image: TImageConfig,
     *     folder: TFolderConfig
     * }
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function getConfig(): array
    {
        return [
            'image' => $this->getImageConfig(),
            'folder' => $this->getFolderConfig(),
        ];
    }

    /**
     * @phpstan-return TImageConfig
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function getImageConfig(): array
    {
        $imageConfig = [
            'showImageFilters' => $this->showImageFilters(),
            'aggregations' => $this->config['image']['aggregations'],
            'enableMultipleDownload' => extension_loaded('zip'),
        ];

        $mappings = [];
        $contentTypeIdentifiers = [];
        $fieldDefinitionIdentifiers = [];

        foreach ($this->config['image']['mappings'] as $contentTypeIdentifier => $mapping) {
            $contentTypeIdentifiers[] = $contentTypeIdentifier;
            $fieldDefinitionIdentifiers[] = $mapping['imageFieldIdentifier'];
            $mappings[$contentTypeIdentifier] = $mapping;

            $contentType = $this->loadContentType($contentTypeIdentifier);
            $mappings[$contentTypeIdentifier]['nameSchemaIdentifiers'] = $this->extractNameSchemaIdentifiers($contentType);
        }

        $imageConfig['mappings'] = $mappings;
        $imageConfig['contentTypeIdentifiers'] = $contentTypeIdentifiers;
        $imageConfig['fieldDefinitionIdentifiers'] = $fieldDefinitionIdentifiers;

        return $imageConfig;
    }

    /**
     * @phpstan-return TFolderConfig
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function getFolderConfig(): array
    {
        $contentTypeIdentifier = $this->config['folder']['contentTypeIdentifier'];

        return [
            'contentTypeIdentifier' => $contentTypeIdentifier,
            'nameSchemaIdentifiers' => $this->extractNameSchemaIdentifiers(
                $this->loadContentType($contentTypeIdentifier)
            ),
        ];
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function loadContentType(string $contentTypeIdentifier): ContentType
    {
        return $this->contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
    }

    /**
     * @return array<string>
     */
    private function extractNameSchemaIdentifiers(ContentType $contentType): array
    {
        return $this->schemaIdentifierExtractor->extract($contentType->nameSchema)['field'] ?? [];
    }

    /**
     * @throws \Ibexa\Bundle\Core\ApiLoader\Exception\InvalidSearchEngine
     */
    private function showImageFilters(): bool
    {
        $config = $this->repositoryConfigurationProvider->getRepositoryConfig();

        $searchEngineAlias = $config['search']['engine'] ?? null;
        if (null === $searchEngineAlias) {
            throw new InvalidSearchEngine(
                sprintf(
                    'Ibexa "%s" Repository has no Search Engine configured',
                    $this->repositoryConfigurationProvider->getCurrentRepositoryAlias()
                )
            );
        }

        return $searchEngineAlias !== 'legacy';
    }
}
