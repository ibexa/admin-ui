<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\AdminUi\DependencyInjection\Compiler;

use Ibexa\AdminUi\Limitation\LimitationFormMapperRegistry;
use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register Limitation form mappers.
 */
class LimitationFormMapperPass implements CompilerPassInterface
{
    private const LIMITATION_MAPPER_FORM_TAG = 'ibexa.admin_ui.limitation.mapper.form';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(LimitationFormMapperRegistry::class)) {
            return;
        }

        $registry = $container->findDefinition(LimitationFormMapperRegistry::class);

        $taggedServiceIds = $container->findTaggedServiceIds(
            self::LIMITATION_MAPPER_FORM_TAG
        );
        foreach ($taggedServiceIds as $serviceId => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['limitationType'])) {
                    throw new LogicException(
                        sprintf(
                            'Service "%s" tagged with "%s" service tag needs a "limitationType" attribute to identify which LimitationType the mapper is for.',
                            $serviceId,
                            self::LIMITATION_MAPPER_FORM_TAG
                        )
                    );
                }

                $registry->addMethodCall('addMapper', [new Reference($serviceId), $attribute['limitationType']]);
            }
        }
    }
}

class_alias(LimitationFormMapperPass::class, 'EzSystems\EzPlatformAdminUiBundle\DependencyInjection\Compiler\LimitationFormMapperPass');
