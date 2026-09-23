<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\AbstractParser;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Configuration parser for the headless mode, where the site is rendered by an external front-end application.
 *
 * Example configuration:
 * ```yaml
 * ibexa:
 *   system:
 *      admin_group: # configuration per siteaccess or siteaccess group
 *          headless:
 *              enabled: true
 *              page_builder:
 *                  preview_url: 'https://frontend.example.com/page-builder'
 *              content:
 *                  preview_url: 'https://frontend.example.com/preview'
 *              configuration_url: 'https://admin.example.com/frontend'
 * ```
 *
 * The node intentionally declares no default values (they live in ezplatform_default_settings.yaml):
 * a key omitted on a siteaccess inherits the value of its group. An explicit `~` on a URL key sets it
 * to null; note that Symfony normalizes `enabled: ~` to `true`, so omit the key to inherit the flag.
 *
 * `content.preview_url` is reserved for the content previews outside of Page Builder and has no consumer yet.
 */
final class Headless extends AbstractParser
{
    private const string NODE = 'headless';

    private const array PARAMETERS = [
        'headless.enabled' => ['enabled'],
        'headless.page_builder.preview_url' => ['page_builder', 'preview_url'],
        'headless.content.preview_url' => ['content', 'preview_url'],
        'headless.configuration_url' => ['configuration_url'],
    ];

    /**
     * @param array<string, mixed> $scopeSettings
     */
    public function mapConfig(
        array &$scopeSettings,
        mixed $currentScope,
        ContextualizerInterface $contextualizer
    ): void {
        if (!isset($scopeSettings[self::NODE]) || !is_array($scopeSettings[self::NODE])) {
            return;
        }

        $settings = $scopeSettings[self::NODE];

        foreach (self::PARAMETERS as $parameterName => $path) {
            if (!$this->hasPath($settings, $path)) {
                continue;
            }

            $contextualizer->setContextualParameter(
                $parameterName,
                $currentScope,
                $this->getPath($settings, $path)
            );
        }
    }

    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->arrayNode(self::NODE)
                ->info('Headless mode configuration.')
                ->children()
                    ->booleanNode('enabled')
                        ->info('Enables the headless mode for the siteaccess.')
                    ->end()
                    ->arrayNode('page_builder')
                        ->children()
                            ->scalarNode('preview_url')
                                ->info('Front-end application URL loaded into the Page Builder preview iframe.')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('content')
                        ->children()
                            ->scalarNode('preview_url')
                                ->info('Front-end application URL used for content preview.')
                            ->end()
                        ->end()
                    ->end()
                    ->scalarNode('configuration_url')
                        ->info('URL of the front-end application configuration page.')
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array<string, mixed> $settings
     * @param string[] $path
     */
    private function hasPath(array $settings, array $path): bool
    {
        foreach ($path as $key) {
            if (!is_array($settings) || !array_key_exists($key, $settings)) {
                return false;
            }

            $settings = $settings[$key];
        }

        return true;
    }

    /**
     * @param array<string, mixed> $settings
     * @param string[] $path
     */
    private function getPath(array $settings, array $path): mixed
    {
        $value = $settings;
        foreach ($path as $key) {
            $value = $value[$key];
        }

        return $value;
    }
}
