<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser;

use Ibexa\AdminUi\UserSetting\FocusMode;
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
 *              default_focus_mode: on
 * ```
 */
final class AdminUiParser extends AbstractParser
{
    private const MODES = [
        'off' => FocusMode::FOCUS_MODE_OFF,
        'on' => FocusMode::FOCUS_MODE_ON,
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
            ->enumNode('default_focus_mode')
                ->info('Default focus mode value')
                ->values(['on', 'off'])
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
        $userMode = $settings['default_focus_mode'];

        if (!array_key_exists($userMode, self::MODES)) {
            return;
        }

        $contextualizer->setContextualParameter(
            'admin_ui.default_focus_mode',
            $currentScope,
            self::MODES[$userMode]
        );
    }
}
