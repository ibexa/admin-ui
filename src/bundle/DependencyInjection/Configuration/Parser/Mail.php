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

/*
 * Example configuration:
 * ```yaml
 * ibexa:
 *   system:
 *      default: # configuration per siteaccess or siteaccess group
 *          mail:
 *              support_email: support@example.com # null hides the "Contact support" line
 * ```
 */
final class Mail extends AbstractParser
{
    public const string SUPPORT_EMAIL_PARAM = 'mail.support_email';

    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->arrayNode('mail')
                ->info('Transactional email settings')
                ->children()
                    ->scalarNode('support_email')
                        ->info('Address linked from the "Contact support" line in the email footer. Set to null to hide the line.')
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
        if (!isset($scopeSettings['mail']) || !array_key_exists('support_email', $scopeSettings['mail'])) {
            return;
        }

        $contextualizer->setContextualParameter(
            self::SUPPORT_EMAIL_PARAM,
            $currentScope,
            $scopeSettings['mail']['support_email']
        );
    }
}
