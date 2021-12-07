<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\DependencyInjection\Compiler;

use Ibexa\AdminUi\UI\Config\Aggregator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Supplies config Providers to the Aggregator.
 */
class UiConfigProviderPass implements CompilerPassInterface
{
    public const TAG_CONFIG_PROVIDER = 'ezplatform.admin_ui.config_provider';

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(Aggregator::class)) {
            return;
        }

        $aggregatorDefinition = $container->getDefinition(Aggregator::class);
        $taggedServiceIds = $container->findTaggedServiceIds(self::TAG_CONFIG_PROVIDER);

        foreach ($taggedServiceIds as $taggedServiceId => $tags) {
            foreach ($tags as $tag) {
                $key = $tag['key'] ?? $taggedServiceId;
                $aggregatorDefinition->addMethodCall('addProvider', [$key, new Reference($taggedServiceId)]);
            }
        }
    }
}

class_alias(UiConfigProviderPass::class, 'EzSystems\EzPlatformAdminUiBundle\DependencyInjection\Compiler\UiConfigProviderPass');
