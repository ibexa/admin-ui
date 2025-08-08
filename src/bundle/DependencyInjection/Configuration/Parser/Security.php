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
 * Configuration parser for system security configuration.
 *
 * Example configuration:
 * ```yaml
 * ibexa:
 *   system:
 *      default: # configuration per siteaccess or siteaccess group
 *          security:
 *              token_interval_spec: 'PT1H'
 * ```
 */
final class Security extends AbstractParser
{
    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->arrayNode('security')
                ->info('System security configuration.')
                ->children()
                    ->scalarNode('token_interval_spec')
                        ->info('Token ttl as DateInterval. See http://php.net/manual/dateinterval.construct.php')
                        ->isRequired()
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
        if (empty($scopeSettings['security'])) {
            return;
        }

        $settings = $scopeSettings['security'];
        $keys = ['token_interval_spec'];

        foreach ($keys as $key) {
            if (empty($settings[$key])) {
                continue;
            }

            $contextualizer->setContextualParameter(
                sprintf('security.%s', $key),
                $currentScope,
                $settings[$key]
            );
        }
    }
}
