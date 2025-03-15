<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ContentType;

use Ibexa\AdminUi\Form\Type\FieldDefinition\FieldDefinitionType;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FieldDefinitionsCollectionType extends AbstractType
{
    private FieldsGroupsList $fieldsGroupsList;

    /**
     * @param \Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList $fieldsGroupsListHelper
     */
    public function __construct(FieldsGroupsList $fieldsGroupsListHelper)
    {
        $this->fieldsGroupsList = $fieldsGroupsListHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($this->fieldsGroupsList->getGroups() as $identifier => $group) {
            if ($identifier === '') {
                $identifier = $this->fieldsGroupsList->getDefaultGroup();
            }

            $builder->add($identifier, CollectionType::class, [
                'entry_type' => FieldDefinitionType::class,
                'entry_options' => [
                    'languageCode' => $options['languageCode'],
                    'mainLanguageCode' => $options['mainLanguageCode'],
                    'block_prefix' => 'content_type_field_definition',
                ],
                'label' => /** @Desc("Content Field definitions") */ 'content_type.field_definitions_data',
                'allow_delete' => true,
                'prototype' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'ibexa_content_type',
                'mainLanguageCode' => null,
            ])
            ->setDefined(['mainLanguageCode'])
            ->setAllowedTypes('mainLanguageCode', ['null', 'string'])
            ->setRequired(['languageCode']);
    }
}
