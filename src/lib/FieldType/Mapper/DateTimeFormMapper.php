<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\FieldType\Mapper;

use Ibexa\AdminUi\FieldType\FieldDefinitionFormMapperInterface;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\AdminUi\Form\Type\DateTimeIntervalType;
use Ibexa\Core\FieldType\DateAndTime\Type;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormMapper for ezdatetime FieldType.
 */
class DateTimeFormMapper implements FieldDefinitionFormMapperInterface
{
    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data): void
    {
        $isTranslation = $data->contentTypeData->languageCode !== $data->contentTypeData->mainLanguageCode;
        $fieldDefinitionForm
            ->add('useSeconds', CheckboxType::class, [
                'required' => false,
                'property_path' => 'fieldSettings[useSeconds]',
                'label' => /** @Desc("Use seconds") */ 'field_definition.ezdatetime.use_seconds',
                'disabled' => $isTranslation,
            ])
            ->add('defaultType', ChoiceType::class, [
                'choices' => [
                    /** @Desc("Empty") */
                    'field_definition.ezdatetime.default_type_empty' => Type::DEFAULT_EMPTY,
                    /** @Desc("Current datetime") */
                    'field_definition.ezdatetime.default_type_current' => Type::DEFAULT_CURRENT_DATE,
                    /** @Desc("Adjusted current datetime") */
                    'field_definition.ezdatetime.default_type_adjusted' => Type::DEFAULT_CURRENT_DATE_ADJUSTED,
                ],
                'expanded' => true,
                'required' => true,
                'property_path' => 'fieldSettings[defaultType]',
                'label' => /** @Desc("Default value") */ 'field_definition.ezdatetime.default_type',
                'disabled' => $isTranslation,
            ])
            ->add('dateInterval', DateTimeIntervalType::class, [
                'required' => false,
                'property_path' => 'fieldSettings[dateInterval]',
                'label' => /** @Desc("Current date and time adjusted by") */ 'field_definition.ezdatetime.date_interval',
                'disabled' => $isTranslation,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'ibexa_content_type',
            ]);
    }
}
