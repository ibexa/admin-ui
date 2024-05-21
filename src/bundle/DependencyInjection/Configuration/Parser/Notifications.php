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
 * Configuration parser for subtree related operations.
 *
 * Example configuration:
 * ```yaml
 * ibexa:
 *   system:
 *      admin_group: # configuration per siteaccess or siteaccess group
 *          notifications:
 *              warning: # type of notification
 *                  timeout: 5000 # in milliseconds
 *          notification_count:
 *              interval: 60000 # in milliseconds
 * ```
 */
class Notifications extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer)
    {
        if (!empty($scopeSettings['notifications'])) {
            $settings = $scopeSettings['notifications'];
            $nodes = ['timeout'];

            foreach ($settings as $type => $config) {
                foreach ($nodes as $key) {
                    if (!isset($config[$key]) || empty($config[$key])) {
                        continue;
                    }

                    $contextualizer->setContextualParameter(
                        sprintf('notifications.%s.%s', $type, $key),
                        $currentScope,
                        $config[$key]
                    );
                }
            }
        }
        if (!empty($scopeSettings['notification_count']) && !empty($scopeSettings['notification_count']['interval'])) {
            $contextualizer->setContextualParameter(
                'notification_count.interval',
                $currentScope,
                $scopeSettings['notification_count']['interval']
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('notifications')
                ->useAttributeAsKey('type')
                ->info('AdminUI notifications configuration.')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('timeout')
                            ->info('Time in milliseconds notifications should disappear after.')
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('notification_count')
                ->children()
                    ->scalarNode('interval')
                        ->info('Time in milliseconds between notification count refreshment.')
                    ->end()
                ->end()
            ->end();
    }
}
