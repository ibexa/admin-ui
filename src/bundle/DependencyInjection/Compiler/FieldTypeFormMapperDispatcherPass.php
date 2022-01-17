<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Bundle\AdminUi\DependencyInjection\Compiler;

use Ibexa\AdminUi\FieldType\FieldTypeDefinitionFormMapperDispatcher;
use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register FieldType form mappers in the mapper dispatcher.
 */
class FieldTypeFormMapperDispatcherPass implements CompilerPassInterface
{
    public const FIELD_TYPE_FORM_MAPPER_DISPATCHER = FieldTypeDefinitionFormMapperDispatcher::class;
    public const FIELD_TYPE_FORM_MAPPER_DEFINITION_SERVICE_TAG = 'ibexa.admin_ui.field_type.form.mapper.definition';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::FIELD_TYPE_FORM_MAPPER_DISPATCHER)) {
            return;
        }

        $dispatcherDefinition = $container->findDefinition(self::FIELD_TYPE_FORM_MAPPER_DISPATCHER);

        $serviceTags = $container->findTaggedServiceIds(
            self::FIELD_TYPE_FORM_MAPPER_DEFINITION_SERVICE_TAG
        );
        foreach ($serviceTags as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['fieldType'])) {
                    throw new LogicException(
                        sprintf(
                            '`%s` service tag needs a "fieldType" attribute to identify which Field Type the mapper is for.',
                            self::FIELD_TYPE_FORM_MAPPER_DEFINITION_SERVICE_TAG
                        )
                    );
                }

                $dispatcherDefinition->addMethodCall('addMapper', [new Reference($id), $tag['fieldType']]);
            }
        }
    }
}

class_alias(FieldTypeFormMapperDispatcherPass::class, 'EzSystems\EzPlatformAdminUiBundle\DependencyInjection\Compiler\FieldTypeFormMapperDispatcherPass');
