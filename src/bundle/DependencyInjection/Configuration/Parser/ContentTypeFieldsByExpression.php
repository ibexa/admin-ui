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
 * Configuration parser for mapping content type fields expressions.
 *
 * Example configuration:
 *
 * ```yaml
 * ibexa:
 *   system:
 *      default:
 *          content_type_field_type_groups:
 *              configurations:
 *                  vectorizable_fields: [ezstring, eztext]
 * ```
 */
final class ContentTypeFieldsByExpression extends AbstractParser
{
    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->arrayNode('content_type_field_type_groups')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('configurations')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->scalarPrototype()->end()
                        ->end()
                        ->defaultValue([])
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array<string,mixed> $scopeSettings
     */
    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer): void
    {
        if (!isset($scopeSettings['content_type_field_type_groups'])) {
            return;
        }

        $configurations = $scopeSettings['content_type_field_type_groups']['configurations'] ?? [];
        foreach ($configurations as $name => $config) {
            $contextualizer->setContextualParameter(
                "content_type_field_type_groups.configurations.$name",
                $currentScope,
                $config
            );
        }
    }
}
