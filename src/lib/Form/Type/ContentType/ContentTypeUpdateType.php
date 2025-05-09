<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Type\ContentType;

use Ibexa\AdminUi\Form\Data\ContentTypeData;
use Ibexa\AdminUi\Form\DataTransformer\TranslatablePropertyTransformer;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for ContentType update.
 */
class ContentTypeUpdateType extends AbstractType
{
    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_content_forms_contenttype_update';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ContentTypeData::class,
                'translation_domain' => 'ibexa_content_type',
                'mainLanguageCode' => null,
            ])
            ->setDefined(['mainLanguageCode'])
            ->setAllowedTypes('mainLanguageCode', ['null', 'string'])
            ->setRequired(['languageCode']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $hasFieldDefinition = count($options['data']->fieldDefinitionsData) > 0;
        $isTranslation = $options['mainLanguageCode'] !== $options['languageCode'];

        $translatablePropertyTransformer = new TranslatablePropertyTransformer($options['languageCode']);
        $builder
            ->add(
                $builder
                    ->create('name', TextType::class, [
                        'property_path' => 'names',
                        'label' => /** @Desc("Name") */ 'content_type.name',
                    ])
                    ->addModelTransformer($translatablePropertyTransformer)
            )
            ->add('identifier', TextType::class, [
                'label' => /** @Desc("Identifier") */ 'content_type.identifier',
                'disabled' => $isTranslation,
            ])
            ->add(
                $builder
                    ->create('description', TextType::class, [
                        'property_path' => 'descriptions',
                        'required' => false,
                        'label' => /** @Desc("Description") */ 'content_type.description',
                    ])
                    ->addModelTransformer($translatablePropertyTransformer)
            )
            ->add('nameSchema', TextType::class, [
                'required' => false,
                'label' => /** @Desc("Content name pattern") */ 'content_type.name_schema',
                'disabled' => $isTranslation,
            ])
            ->add('urlAliasSchema', TextType::class, [
                'required' => false,
                'label' => /** @Desc("URL alias name pattern") */ 'content_type.url_alias_schema',
                'empty_data' => false,
                'disabled' => $isTranslation,
            ])
            ->add('isContainer', CheckboxType::class, [
                'required' => false,
                'label' => /** @Desc("Container") */ 'content_type.is_container',
                'disabled' => $isTranslation,
            ])
            ->add('defaultSortField', SortFieldChoiceType::class, [
                'label' => /** @Desc("Sort children by default by") */ 'content_type.default_sort_field',
                'disabled' => $isTranslation,
            ])
            ->add('defaultSortOrder', SortOrderChoiceType::class, [
                'label' => /** @Desc("Sort children by default in order") */ 'content_type.default_sort_order',
                'disabled' => $isTranslation,
            ])
            ->add('defaultAlwaysAvailable', CheckboxType::class, [
                'required' => false,
                'label' => /** @Desc("Make content available even with missing translations") */ 'content_type.default_always_available',
                'disabled' => $isTranslation,
            ])
            ->add('fieldDefinitionsData', FieldDefinitionsCollectionType::class, [
                'languageCode' => $options['languageCode'],
                'mainLanguageCode' => $options['mainLanguageCode'],
            ])
            ->add('metaFieldDefinitionsData', FieldDefinitionsCollectionType::class, [
                'languageCode' => $options['languageCode'],
                'mainLanguageCode' => $options['mainLanguageCode'],
                'block_prefix' => 'content_type_meta_field_definitions_data',
            ])
            ->add('saveContentType', SubmitType::class, ['label' => /** @Desc("Apply") */ 'content_type.save'])
            ->add('removeDraft', SubmitType::class, ['label' => /** @Desc("Cancel") */ 'content_type.remove_draft', 'validation_groups' => false])
            ->add('publishContentType', SubmitType::class, [
                'label' => /** @Desc("OK") */ 'content_type.publish',
            ])
            ->add('publishAndEditContentType', SubmitType::class, [
                'label' => /** @Desc("Save") */ 'content_type.publish_and_edit',
            ])
        ;
    }
}
