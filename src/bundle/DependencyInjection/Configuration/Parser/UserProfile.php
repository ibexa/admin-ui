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

final class UserProfile extends AbstractParser
{
    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->arrayNode('user_profile')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')
                        ->defaultFalse()
                    ->end()
                    ->arrayNode('content_types')
                        ->scalarPrototype()->end()
                        ->defaultValue(['editor'])
                        ->example(['editor', 'administrator'])
                    ->end()
                    ->arrayNode('field_groups')
                        ->defaultValue(['about', 'contact'])
                        ->scalarPrototype()->end()
                        ->example(['about', 'contact'])
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array<string, mixed> $scopeSettings
     * @param string $currentScope
     */
    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer): void
    {
        if (empty($scopeSettings['user_profile'])) {
            return;
        }

        $contextualizer->setContextualParameter(
            'user_profile.enabled',
            $currentScope,
            $scopeSettings['user_profile']['enabled']
        );

        $contextualizer->setContextualParameter(
            'user_profiler.content_types',
            $currentScope,
            $scopeSettings['user_profile']['content_types']
        );

        $contextualizer->setContextualParameter(
            'user_profile.field_groups',
            $currentScope,
            $scopeSettings['user_profile']['field_groups']
        );
    }
}
