<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Type\FieldDefinition;

use Ibexa\AdminUi\Config\AdminUiForms\ContentTypeFieldTypesResolverInterface;
use Ibexa\AdminUi\FieldType\FieldTypeDefinitionFormMapperDispatcherInterface;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\AdminUi\Form\DataTransformer\TranslatablePropertyTransformer;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Core\Repository\Strategy\ContentThumbnail\Field\ThumbnailStrategy;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for FieldDefinition update.
 */
class FieldDefinitionType extends AbstractType
{
    private ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver;

    /** @var \Ibexa\AdminUi\FieldType\FieldTypeDefinitionFormMapperDispatcherInterface */
    private $fieldTypeMapperDispatcher;

    /** @var \Ibexa\Contracts\Core\Repository\FieldTypeService */
    private $fieldTypeService;

    /** @var \Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList */
    private $groupsList;

    /** @var \Ibexa\Contracts\Core\Repository\Strategy\ContentThumbnail\Field\ThumbnailStrategy */
    private $thumbnailStrategy;

    public function __construct(
        ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver,
        FieldTypeDefinitionFormMapperDispatcherInterface $fieldTypeMapperDispatcher,
        FieldTypeService $fieldTypeService,
        ThumbnailStrategy $thumbnailStrategy
    ) {
        $this->contentTypeFieldTypesResolver = $contentTypeFieldTypesResolver;
        $this->fieldTypeMapperDispatcher = $fieldTypeMapperDispatcher;
        $this->fieldTypeService = $fieldTypeService;
        $this->thumbnailStrategy = $thumbnailStrategy;
    }

    public function setGroupsList(FieldsGroupsList $groupsList)
    {
        $this->groupsList = $groupsList;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => FieldDefinitionData::class,
                'translation_domain' => 'ibexa_content_type',
                'mainLanguageCode' => null,
                'disable_identifier_field' => false,
                'disable_required_field' => false,
                'disable_translatable_field' => false,
                'disable_remove' => false,
            ])
            ->setDefined(['mainLanguageCode'])
            ->setAllowedTypes('mainLanguageCode', ['null', 'string'])
            ->setRequired(['languageCode']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translatablePropertyTransformer = new TranslatablePropertyTransformer($options['languageCode']);
        $isTranslation = $options['languageCode'] !== $options['mainLanguageCode'];

        $builder
            ->add(
                $builder->create(
                    'name',
                    TextType::class,
                    [
                        'property_path' => 'names',
                        'label' => /** @Desc("Name") */ 'field_definition.name',
                    ]
                )
                    ->addModelTransformer($translatablePropertyTransformer)
            )
            ->add(
                'identifier',
                TextType::class,
                [
                    'label' => /** @Desc("Identifier") */ 'field_definition.identifier',
                    'disabled' => $options['disable_identifier_field'] || $isTranslation,
                ]
            )
            ->add(
                $builder->create('description', TextType::class, [
                    'property_path' => 'descriptions',
                    'required' => false,
                    'label' => /** @Desc("Description") */ 'field_definition.description',
                ])
                    ->addModelTransformer($translatablePropertyTransformer)
            )
            ->add('isRequired', CheckboxType::class, [
                'required' => false,
                'label' => /** @Desc("Required") */ 'field_definition.is_required',
                'disabled' => $options['disable_required_field'] || $isTranslation,
            ])
            ->add('isTranslatable', CheckboxType::class, [
                'required' => false,
                'label' => /** @Desc("Translatable") */ 'field_definition.is_translatable',
                'disabled' => $options['disable_translatable_field'] || $isTranslation,
            ])
            ->add(
                'fieldGroup',
                HiddenType::class,
            )
            ->add('position', IntegerType::class, [
                'label' => /** @Desc("Position") */ 'field_definition.position',
                'disabled' => $isTranslation,
            ]);

        // Hook on form generation for specific FieldType needs
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Ibexa\AdminUi\Form\Data\FieldDefinitionData $data */
            $data = $event->getData();
            $form = $event->getForm();
            $fieldTypeIdentifier = $data->getFieldTypeIdentifier();
            $fieldType = $this->fieldTypeService->getFieldType($fieldTypeIdentifier);
            $isTranslation = $data->contentTypeData->languageCode !== $data->contentTypeData->mainLanguageCode;
            if (
                in_array(
                    $fieldTypeIdentifier,
                    $this->contentTypeFieldTypesResolver->getMetaFieldTypeIdentifiers(),
                    true
                )
            ) {
                $form->add(
                    'enabled',
                    CheckboxType::class,
                    [
                        'required' => true,
                        'label' => false,
                        'block_prefix' => 'content_type_meta_field_definition_enabled',
                    ]
                );
            }

            // isSearchable field should be present only if the FieldType allows it.
            $form->add('isSearchable', CheckboxType::class, [
                'required' => false,
                'disabled' => !$fieldType->isSearchable() || $isTranslation,
                'label' => /** @Desc("Searchable") */ 'field_definition.is_searchable',
            ]);

            $form->add('isThumbnail', CheckboxType::class, [
                'required' => false,
                'label' => /** @Desc("Can be a thumbnail") */ 'field_definition.is_thumbnail',
                'disabled' => $isTranslation || !$this->thumbnailStrategy->hasStrategy($fieldTypeIdentifier),
            ]);

            // Let fieldType mappers do their jobs to complete the form.
            $this->fieldTypeMapperDispatcher->map($form, $data);
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['disable_remove'] = $options['disable_remove'];
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'ezplatform_content_forms_fielddefinition_update';
    }
}

class_alias(FieldDefinitionType::class, 'EzSystems\EzPlatformAdminUi\Form\Type\FieldDefinition\FieldDefinitionType');
