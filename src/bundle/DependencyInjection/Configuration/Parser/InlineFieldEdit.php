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
 * Configuration parser for inline (quick) editing of simple Field values from the Content View.
 *
 * Example configuration:
 * ```yaml
 * ibexa:
 *   system:
 *      admin_group: # configuration per siteaccess or siteaccess group
 *          inline_field_edit:
 *              enabled: true
 *              supported_field_types: [ibexa_string, ibexa_text]
 *              excluded_field_types: [ibexa_text]
 * ```
 */
final class InlineFieldEdit extends AbstractParser
{
    private const array DEFAULT_SUPPORTED_FIELD_TYPES = [
        'ibexa_string',
        'ibexa_text',
        'ibexa_email',
        'ibexa_integer',
        'ibexa_float',
        'ibexa_boolean',
        'ibexa_date',
        'ibexa_datetime',
        'ibexa_time',
    ];

    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->arrayNode('inline_field_edit')
                ->info('Inline editing of simple Field values directly from the Content View.')
                ->children()
                    ->booleanNode('enabled')
                        ->info('Enables inline editing of Field values from the Content View.')
                        ->defaultFalse()
                    ->end()
                    ->arrayNode('supported_field_types')
                        ->info('List of Field Type identifiers eligible for inline editing.')
                        ->defaultValue(self::DEFAULT_SUPPORTED_FIELD_TYPES)
                        ->scalarPrototype()->end()
                    ->end()
                    ->arrayNode('excluded_field_types')
                        ->info(
                            'List of Field Type identifiers to exclude from inline editing, ' .
                            'subtracted from "supported_field_types".'
                        )
                        ->defaultValue([])
                        ->scalarPrototype()->end()
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
        if (empty($scopeSettings['inline_field_edit'])) {
            return;
        }

        $contextualizer->setContextualParameter(
            'inline_field_edit.enabled',
            $currentScope,
            $scopeSettings['inline_field_edit']['enabled']
        );

        $contextualizer->setContextualParameter(
            'inline_field_edit.supported_field_types',
            $currentScope,
            $scopeSettings['inline_field_edit']['supported_field_types']
        );

        $contextualizer->setContextualParameter(
            'inline_field_edit.excluded_field_types',
            $currentScope,
            $scopeSettings['inline_field_edit']['excluded_field_types']
        );
    }
}
