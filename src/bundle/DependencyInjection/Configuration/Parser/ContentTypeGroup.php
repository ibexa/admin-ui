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
 * Configuration parser for content type group icons.
 *
 * Example configuration:
 *
 * ```yaml
 * ibexa:
 *   system:
 *      default: # configuration per siteaccess or siteaccess group
 *          content_type_group:
 *             foo:
 *                thumbnail: '/assets/images/foo.svg'
 *             bar:
 *                thumbnail: '/assets/images/bar.svg'
 * ```
 */
class ContentTypeGroup extends AbstractParser
{
    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->arrayNode('content_type_group')
                ->useAttributeAsKey('identifier')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('thumbnail')->defaultNull()->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array<string,mixed> $scopeSettings
     */
    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer): void
    {
        if (empty($scopeSettings['content_type_group'])) {
            return;
        }

        foreach ($scopeSettings['content_type_group'] as $identifier => $config) {
            $contextualizer->setContextualParameter("content_type_group.$identifier", $currentScope, $config);
        }
    }
}
