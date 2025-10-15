<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ContentType\Translation;

use Ibexa\AdminUi\Form\Data\ContentType\Translation\TranslationRemoveData;
use Ibexa\AdminUi\Form\Type\Content\ContentTypeType;
use Ibexa\AdminUi\Form\Type\ContentTypeGroup\ContentTypeGroupType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<\Ibexa\AdminUi\Form\Data\ContentType\Translation\TranslationRemoveData>
 */
class TranslationRemoveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'content_type',
                ContentTypeType::class,
                [
                    'label' => false,
                    'attr' => [
                        'hidden' => true,
                    ],
                ]
            )
            ->add(
                'content_type_group',
                ContentTypeGroupType::class,
                ['label' => false]
            )
            ->add(
                'language_codes',
                CollectionType::class,
                [
                    'label' => false,
                    'allow_add' => true,
                    'entry_type' => CheckboxType::class,
                    'entry_options' => ['label' => false, 'required' => false],
                ]
            )
            ->add(
                'remove',
                SubmitType::class,
                [
                    'attr' => ['hidden' => true],
                    'label' => /** @Desc("Remove translation") */ 'content_translation_remove_form.remove',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TranslationRemoveData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
