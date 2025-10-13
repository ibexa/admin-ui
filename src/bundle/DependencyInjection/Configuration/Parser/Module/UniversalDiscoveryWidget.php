<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser\Module;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\AbstractParser;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Configuration parser for UDW module.
 */
final class UniversalDiscoveryWidget extends AbstractParser
{
    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->arrayNode('universal_discovery_widget_module')
                ->info('UDW module configuration')
                ->children()
                    ->arrayNode('configuration')
                        ->isRequired()
                        ->useAttributeAsKey('scope_name')
                        ->variablePrototype()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array<string, mixed> $scopeSettings
     */
    public function mapConfig(
        array &$scopeSettings,
        mixed $currentScope,
        ContextualizerInterface $contextualizer
    ): void {
        if (empty($scopeSettings['universal_discovery_widget_module'])) {
            return;
        }

        $settings = $scopeSettings['universal_discovery_widget_module'];

        $contextualizer->setContextualParameter(
            'universal_discovery_widget_module.configuration',
            $currentScope,
            $settings['configuration']
        );
    }
}
