<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Language;

use Ibexa\AdminUi\Form\Data\Language\LanguageUpdateData;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<\Ibexa\AdminUi\Form\Data\Language\LanguageUpdateData>
 */
class LanguageUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'language',
                LanguageType::class,
                ['label' => false]
            )
            ->add(
                'name',
                TextType::class,
                ['label' => /** @Desc("Name") */ 'ezplatform.language.update.name']
            )
            ->add(
                'enabled',
                CheckboxType::class,
                [
                    'label' => /** @Desc("Enabled") */ 'ezplatform.language.update.enabled',
                    'required' => false,
                ]
            )
            ->add(
                'save_and_close',
                SubmitType::class
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LanguageUpdateData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
