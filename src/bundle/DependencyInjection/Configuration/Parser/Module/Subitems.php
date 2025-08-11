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
 * Configuration parser for Subitems module.
 *
 * Example configuration:
 * ```yaml
 * ibexa:
 *   system:
 *      default: # configuration per siteaccess or siteaccess group
 *          subitems_module:
 *              limit: 10
 * ```
 */
final class Subitems extends AbstractParser
{
    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->arrayNode('subitems_module')
            ->info('Subitems module configuration')
            ->children()
            ->integerNode('limit')->isRequired()->defaultValue(10)->end()
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
        if (empty($scopeSettings['subitems_module'])) {
            return;
        }

        $settings = $scopeSettings['subitems_module'];

        if (!isset($settings['limit'])) {
            return;
        }

        $contextualizer->setContextualParameter(
            'subitems_module.limit',
            $currentScope,
            $settings['limit']
        );
    }
}
