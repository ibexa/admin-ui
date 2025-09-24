<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\DependencyInjection\Compiler;

use Ibexa\AdminUi\Component\Registry;
use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @deprecated 4.6.19 The {@see \Ibexa\Bundle\AdminUi\DependencyInjection\Compiler\ComponentPass} class is deprecated, will be removed in 6.0.
 * Use {@see \Ibexa\Bundle\TwigComponents\DependencyInjection\Compiler\ComponentPass} instead
 */
final readonly class ComponentPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public const string TAG_NAME = 'ibexa.admin_ui.component';

    /**
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException When a service is abstract
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException When a tag is missing 'group' attribute
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(Registry::class)) {
            return;
        }

        $registryDefinition = $container->getDefinition(Registry::class);
        $services = $this->findAndSortTaggedServices(self::TAG_NAME, $container);

        foreach ($services as $serviceReference) {
            $id = (string)$serviceReference;
            $definition = $container->getDefinition($id);
            $tags = $definition->getTag(static::TAG_NAME);

            foreach ($tags as $tag) {
                if (!isset($tag['group'])) {
                    throw new InvalidArgumentException($id, 'Tag ' . self::TAG_NAME . ' must contain a "group" argument.');
                }

                $id = $tag['id'] ?? $id;
                $registryDefinition->addMethodCall('addComponent', [$tag['group'], $id, $serviceReference]);
            }
        }
    }
}
