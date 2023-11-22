<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser;

use Ibexa\AdminUi\UserSetting\UserMode;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\AbstractParser;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Configuration parser for user modes.
 *
 * ```yaml
 * ibexa:
 *   system:
 *      default: # configuration per siteaccess or siteaccess group
 *          admin_ui:
 *              default_user_mode: smart
 * ```
 */
final class AdminUiParser extends AbstractParser
{
    private const MODES = [
        'expert' => UserMode::EXPERT,
        'smart' => UserMode::SMART,
    ];

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

        $this->addUserModeParameters($settings, $currentScope, $contextualizer);
    }

    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $root = $nodeBuilder->arrayNode('admin_ui');
        $root->children()
            ->enumNode('default_user_mode')
                ->info('Default user mode setting')
                ->values(['smart', 'expert'])
            ->end()
        ->end();
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function addUserModeParameters(
        array $settings,
        string $currentScope,
        ContextualizerInterface $contextualizer
    ): void {
        $userMode = $settings['default_user_mode'];

        if (false === array_key_exists($userMode, self::MODES)) {
            return;
        }

        $contextualizer->setContextualParameter(
            'admin_ui.default_user_mode',
            $currentScope,
            self::MODES[$userMode]
        );
    }
}
