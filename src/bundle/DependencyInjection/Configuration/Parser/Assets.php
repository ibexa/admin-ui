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

class Assets extends AbstractParser
{
    private const ASSETS_NODE = 'assets';

    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->arrayNode(self::ASSETS_NODE)
                ->validate()
                    ->ifTrue(static function (array $assetsConfig): bool {
                        return !isset($assetsConfig['icon_sets'][$assetsConfig['default_icon_set']]);
                    })
                    ->thenInvalid("Default Icon Set is not defined in 'icon_sets' configuration.")
                ->end()
                ->children()
                    ->arrayNode('icon_sets')
                        ->validate()
                            ->ifTrue(static function (array $value): bool {
                                foreach ($value as $set => $path) {
                                    $file = new \SplFileInfo($path);

                                    if ($file->getExtension() !== 'svg') {
                                        return true;
                                    }
                                }

                                return false;
                            })
                            ->thenInvalid('Icon Path is invalid. Please provide *.svg file.')
                        ->end()
                        ->useAttributeAsKey('name')
                        ->scalarPrototype()->end()
                    ->end()
                    ->scalarNode('default_icon_set')
                        ->isRequired()
                    ->end()
                    ->arrayNode('icon_aliases')
                        ->useAttributeAsKey('name')
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end();
    }

    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer): void
    {
        if (empty($scopeSettings[self::ASSETS_NODE])) {
            return;
        }

        foreach ($scopeSettings[self::ASSETS_NODE] as $identifier => $config) {
            $contextualizer->setContextualParameter(sprintf('%s.%s', self::ASSETS_NODE, $identifier), $currentScope, $config);
        }
    }
}
