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
 *      default: # configuration per siteaccess or siteaccess group
 *          subtree_operations:
 *              copy_subtree:
 *                  limit: 200
 * ```
 */
class SubtreeOperations extends AbstractParser
{
    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer): void
    {
        if (!isset($scopeSettings['subtree_operations']['copy_subtree']['limit'])) {
            return;
        }

        $contextualizer->setContextualParameter(
            'subtree_operations.copy_subtree.limit',
            $currentScope,
            $scopeSettings['subtree_operations']['copy_subtree']['limit']
        );
    }

    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->arrayNode('subtree_operations')
                ->info('Subtree related operations configuration.')
                ->children()
                    ->arrayNode('copy_subtree')
                        ->children()
                            ->integerNode('limit')
                                ->info('Number of items that can be copied at once, -1 for no limit, 0 to disable copying.')
                                ->isRequired()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('query_subtree')
                    ->children()
                        ->integerNode('limit')
                            ->info('Limit the total count of items queried for when calculating the the number of direct children a node has. -1 for no limit. Default is 500 for performance reasons.')
                            ->defaultValue(500)
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}

class_alias(SubtreeOperations::class, 'EzSystems\EzPlatformAdminUiBundle\DependencyInjection\Configuration\Parser\SubtreeOperations');
