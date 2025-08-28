<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\FieldType\Mapper;

use Ibexa\AdminUi\FieldType\FieldDefinitionFormMapperInterface;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\ContentForms\Form\Type\FieldType\IntegerFieldType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormMapper for ibexa_integer FieldType.
 */
final readonly class IntegerFormMapper implements FieldDefinitionFormMapperInterface
{
    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data): void
    {
        $isTranslation = $data->contentTypeData->languageCode !== $data->contentTypeData->mainLanguageCode;
        $defaultValueForm = $fieldDefinitionForm
            ->getConfig()
            ->getFormFactory()
            ->createBuilder()
            ->create('defaultValue', IntegerFieldType::class, [
                'required' => false,
                'label' => /** @Desc("Default value") */ 'field_definition.ibexa_integer.default_value',
            ])
            ->setAutoInitialize(false)
            ->getForm();

        $fieldDefinitionForm
            ->add(
                'minValue',
                IntegerType::class,
                [
                    'required' => false,
                    'property_path' => 'validatorConfiguration[IntegerValueValidator][minIntegerValue]',
                    'label' => /** @Desc("Minimum value") */ 'field_definition.ibexa_integer.min_value',
                    'disabled' => $isTranslation,
                ]
            )
            ->add(
                'maxValue',
                IntegerType::class,
                [
                    'required' => false,
                    'property_path' => 'validatorConfiguration[IntegerValueValidator][maxIntegerValue]',
                    'label' => /** @Desc("Maximum value") */ 'field_definition.ibexa_integer.max_value',
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
