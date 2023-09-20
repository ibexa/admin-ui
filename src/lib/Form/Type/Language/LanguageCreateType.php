<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Form\Type\Language;

use Ibexa\AdminUi\Form\Data\Language\LanguageCreateData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LanguageCreateType extends AbstractType
{
    public const BTN_SAVE = 'save';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                ['label' => /** @Desc("Name") */ 'ezplatform.language.create.name']
            )
            ->add(
                'languageCode',
                TextType::class,
                ['label' => /** @Desc("Language code") */ 'ezplatform.language.create.language_code']
            )
            ->add(
                'enabled',
                CheckboxType::class,
                [
                    'label' => /** @Desc("Enabled") */ 'ezplatform.language.create.enabled',
                    'required' => false,
                ]
            )
            ->add(
                self::BTN_SAVE,
                SubmitType::class,
                ['label' => /** @Desc("Save") */ 'language.create.save']
            )
            ->add(
                'save_and_close',
                SubmitType::class,
                ['label' => /** @Desc("Save and close") */ 'language.create.save_and_close']
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LanguageCreateData::class,
            'translation_domain' => 'forms',
        ]);
    }
}

class_alias(LanguageCreateType::class, 'EzSystems\EzPlatformAdminUi\Form\Type\Language\LanguageCreateType');
