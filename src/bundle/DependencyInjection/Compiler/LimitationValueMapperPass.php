<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\AdminUi\DependencyInjection\Compiler;

use Ibexa\AdminUi\Limitation\LimitationValueMapperRegistry;
use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register Limitation value mappers.
 */
class LimitationValueMapperPass implements CompilerPassInterface
{
    public const LIMITATION_VALUE_MAPPER_REGISTRY = LimitationValueMapperRegistry::class;
    public const LIMITATION_VALUE_MAPPER_TAG = 'ibexa.admin_ui.limitation.mapper.value';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::LIMITATION_VALUE_MAPPER_REGISTRY)) {
            return;
        }

        $registry = $container->findDefinition(self::LIMITATION_VALUE_MAPPER_REGISTRY);

        foreach ($container->findTaggedServiceIds(self::LIMITATION_VALUE_MAPPER_TAG) as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['limitationType'])) {
                    throw new LogicException(sprintf(
                        'The %s service tag needs a "limitationType" attribute to identify which LimitationType the mapper is for.',
                        self::LIMITATION_VALUE_MAPPER_TAG
                    ));
                }

                $registry->addMethodCall('addMapper', [new Reference($id), $attribute['limitationType']]);
            }
        }
    }
}
