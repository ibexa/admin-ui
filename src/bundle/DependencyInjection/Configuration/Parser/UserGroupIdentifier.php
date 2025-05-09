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
 * Configuration parser for user group identifier configuration.
 *
 * Example configuration:
 * ```yaml
 * ezpublish:
 *   system:
 *      default: # configuration per siteaccess or siteaccess group
 *          user_group_content_type_identifier: ['user_group', 'my_custom_user_group_identifier']
 * ```
 */
class UserGroupIdentifier extends AbstractParser
{
    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     */
    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->arrayNode('user_group_content_type_identifier')
                ->info('User Group content type identifier configuration.')
                ->example(['user_group', 'my_custom_user_group_identifier'])
                ->requiresAtLeastOneElement()
                ->prototype('scalar')->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer): void
    {
        if (empty($scopeSettings['user_group_content_type_identifier'])) {
            return;
        }

        $contextualizer->setContextualParameter(
            'user_group_content_type_identifier',
            $currentScope,
            $scopeSettings['user_group_content_type_identifier']
        );
    }
}
