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
 * @template TConfig of array{
 *     image: array{
 *         fieldDefinitionIdentifiers: array<string>,
 *         contentTypeIdentifiers: array<string>,
 *         aggregations: array<string, array<string, string>>,
 *     },
 *     folder: array{
 *         contentTypeIdentifier: string,
 *     }
 *  }
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
     *     image: array{
     *         fieldDefinitionIdentifiers: array<string>,
     *         contentTypeIdentifiers: array<string>,
     *         aggregations: array<string, array<string, string>>,
     *         showImageFilters: bool,
     *     },
     *     folder: array{
     *         contentTypeIdentifier: string,
     *         nameSchemaIdentifiers: array<string>
     *     }
     * }
     *
     * @throws \Ibexa\Bundle\Core\ApiLoader\Exception\InvalidSearchEngine
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function getConfig(): array
    {
        $widgetConfig = $this->config;
        $widgetConfig['image']['showImageFilters'] = $this->showImageFilters();
        $widgetConfig['folder'] = $this->getFolderConfig();

        return $widgetConfig;
    }

    /**
     * @return array{
     *     contentTypeIdentifier: string,
     *     nameSchemaIdentifiers: array<string>
     * }
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
