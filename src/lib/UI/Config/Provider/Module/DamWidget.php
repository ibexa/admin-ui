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

/**
 * @template TFolderConfig of array{
 *     contentTypeIdentifier: string,
 *     nameFieldIdentifier: string
 * }
 * @template TConfig of array{
 *     image: array{
 *         fieldDefinitionIdentifiers: array<string>,
 *         contentTypeIdentifiers: array<string>,
 *         aggregations: array<string, array<string, string>>,
 *     },
 *     folder: TFolderConfig
 *  }
 */
final class DamWidget implements ProviderInterface
{
    /** @phpstan-var TConfig */
    private array $config;

    private RepositoryConfigurationProvider $repositoryConfigurationProvider;

    /**
     * @phpstan-param TConfig $config
     */
    public function __construct(
        array $config,
        RepositoryConfigurationProvider $repositoryConfigurationProvider
    ) {
        $this->config = $config;
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
    }

    /**
     * @phpstan-return array{
     *     image: array{
     *         fieldDefinitionIdentifiers: array<string>,
     *         contentTypeIdentifiers: array<string>,
     *         aggregations: array<string, array<string, string>>,
     *         showImageFilters: bool,
     *     },
     *     folder: TFolderConfig
     * }
     *
     * @throws \Ibexa\Bundle\Core\ApiLoader\Exception\InvalidSearchEngine
     */
    public function getConfig(): array
    {
        $widgetConfig = $this->config;
        $widgetConfig['image']['showImageFilters'] = $this->showImageFilters();

        return $widgetConfig;
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
