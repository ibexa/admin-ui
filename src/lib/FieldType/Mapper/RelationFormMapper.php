<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\FieldType\Mapper;

use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\ContentForms\Form\Type\RelationType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RelationFormMapper extends AbstractRelationFormMapper
{
    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data): void
    {
        $isTranslation = $data->contentTypeData->languageCode !== $data->contentTypeData->mainLanguageCode;
        $fieldDefinitionForm
            ->add('selectionRoot', RelationType::class, [
                'required' => true,
                'property_path' => 'fieldSettings[selectionRoot]',
                'label' => /** @Desc("Starting Location") */ 'field_definition.ezobjectrelation.selection_root',
            ])
            ->add('rootDefaultLocation', CheckboxType::class, [
                'required' => false,
                'label' => /** @Desc("Root Default Location") */ 'field_definition.ezobjectrelation.root_default_location',
                'property_path' => 'fieldSettings[rootDefaultLocation]',
            ])
            ->add('selectionContentTypes', ChoiceType::class, [
                'choices' => $this->getContentTypesHash(),
                'expanded' => false,
                'multiple' => true,
                'required' => false,
                'property_path' => 'fieldSettings[selectionContentTypes]',
                'label' => /** @Desc("Allowed Content Types") */ 'field_definition.ezobjectrelation.selection_content_types',
                'disabled' => $isTranslation,
            ]);
    }

    /**
     * Fake method to set the translation domain for the extractor.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'ibexa_content_type',
            ]);
    }
}

class_alias(RelationFormMapper::class, 'EzSystems\EzPlatformAdminUi\FieldType\Mapper\RelationFormMapper');
