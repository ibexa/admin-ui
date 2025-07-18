<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\FieldType\Mapper;

use Ibexa\AdminUi\FieldType\FieldDefinitionFormMapperInterface;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\Core\FieldType\Date\Type;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormMapper for ibexa_date FieldType.
 */
class DateFormMapper implements FieldDefinitionFormMapperInterface
{
    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data): void
    {
        $isTranslation = $data->contentTypeData->languageCode !== $data->contentTypeData->mainLanguageCode;
        $fieldDefinitionForm
            ->add(
                'defaultType',
                ChoiceType::class,
                [
                    'choices' => [
                        /** @Desc("Empty") */
                        'field_definition.ibexa_date.default_type_empty' => Type::DEFAULT_EMPTY,
                        /** @Desc("Current date") */
                        'field_definition.ibexa_date.default_type_current' => Type::DEFAULT_CURRENT_DATE,
                    ],
                    'expanded' => true,
                    'required' => true,
                    'property_path' => 'fieldSettings[defaultType]',
                    'label' => /** @Desc("Default value") */ 'field_definition.ibexa_date.default_type',
                    'translation_domain' => 'ibexa_content_type',
                    'disabled' => $isTranslation,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'ibexa_content_type',
            ]);
    }
}
