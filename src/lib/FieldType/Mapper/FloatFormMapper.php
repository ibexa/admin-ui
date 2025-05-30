<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\FieldType\Mapper;

use Ibexa\AdminUi\FieldType\FieldDefinitionFormMapperInterface;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\ContentForms\Form\Type\FieldType\FloatFieldType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormMapper for ibexa_float FieldType.
 */
class FloatFormMapper implements FieldDefinitionFormMapperInterface
{
    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $fieldDefinition): void
    {
        $isTranslation = $fieldDefinition->contentTypeData->languageCode !== $fieldDefinition->contentTypeData->mainLanguageCode;
        $defaultValueForm = $fieldDefinitionForm
            ->getConfig()
            ->getFormFactory()
            ->createBuilder()
            ->create('defaultValue', FloatFieldType::class, [
                'required' => false,
                'label' => /** @Desc("Default value") */ 'field_definition.ibexa_float.default_value',
                'disabled' => $isTranslation,
            ])
            ->setAutoInitialize(false)
            ->getForm();

        $fieldDefinitionForm
            ->add(
                'minValue',
                NumberType::class,
                [
                    'required' => false,
                    'property_path' => 'validatorConfiguration[FloatValueValidator][minFloatValue]',
                    'label' => /** @Desc("Minimum value") */ 'field_definition.ibexa_float.min_value',
                    'disabled' => $isTranslation,
                ]
            )
            ->add(
                'maxValue',
                NumberType::class,
                [
                    'required' => false,
                    'property_path' => 'validatorConfiguration[FloatValueValidator][maxFloatValue]',
                    'label' => /** @Desc("Maximum value") */ 'field_definition.ibexa_float.max_value',
                    'disabled' => $isTranslation,
                ]
            )
            ->add($defaultValueForm);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'ibexa_content_type',
            ]);
    }
}
