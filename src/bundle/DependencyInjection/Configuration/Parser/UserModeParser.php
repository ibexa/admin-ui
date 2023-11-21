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
 * Configuration parser for user modes.
 *
 * @Example configuration:
 *
 * ```yaml
 * ibexa:
 *   system:
 *      default: # configuration per siteaccess or siteaccess group
 *          admin_ui:
 *              default_user_mode: smart
 * ```
 */
final class UserModeParser extends AbstractParser
{
    /**
     * @param array<string, mixed> $scopeSettings
     */
    public function mapConfig(
        array &$scopeSettings,
        $currentScope,
        ContextualizerInterface $contextualizer
    ): void {
        if (empty($scopeSettings['admin_ui'])) {
            return;
        }

        $settings = $scopeSettings['admin_ui'];

        $this->addSuggestionParameters($settings, $currentScope, $contextualizer);
    }

    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $rootProductCatalogNode = $nodeBuilder->arrayNode('admin_ui');
        $rootProductCatalogNode->children()
                ->enumNode('default_user_mode')
                    ->info('Default user mode setting')
                    ->values(['smart', 'expert'])
                ->end()
            ->end();
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function addSuggestionParameters(
        array $settings,
        string $currentScope,
        ContextualizerInterface $contextualizer
    ): void {
        $contextualizer->setContextualParameter(
            'admin_ui.default_user_mode',
            $currentScope,
            $settings['default_user_mode']
        );
    }
}
