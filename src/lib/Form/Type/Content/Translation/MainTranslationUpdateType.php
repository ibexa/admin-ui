<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Content\Translation;

use Ibexa\AdminUi\Form\Data\Content\Translation\MainTranslationUpdateData;
use Ibexa\AdminUi\Form\Type\Content\ContentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MainTranslationUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'content',
                ContentType::class,
                ['label' => false]
            )
            ->add(
                'language_code',
                HiddenType::class,
                ['label' => false]
            )
            ->add(
                'update',
                SubmitType::class,
                [
                    'attr' => ['hidden' => true],
                    'label' => false,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MainTranslationUpdateData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
