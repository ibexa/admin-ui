<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Content\Location;

use Ibexa\AdminUi\Form\Data\Content\Location\ContentLocationRemoveData;
use Ibexa\AdminUi\Form\Type\Content\ContentInfoType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<\Ibexa\AdminUi\Form\Data\Content\Location\ContentLocationRemoveData>
 */
final class ContentLocationRemoveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'content_info',
                ContentInfoType::class,
                ['label' => false]
            )
            ->add(
                'locations',
                CollectionType::class,
                [
                    'entry_type' => CheckboxType::class,
                    'required' => false,
                    'allow_add' => true,
                    'entry_options' => ['label' => false],
                    'label' => false,
                ]
            )
            ->add(
                'remove',
                SubmitType::class,
                [
                    'attr' => ['hidden' => true],
                    'label' => /** @Desc("Delete") */
                        'content_location_remove_form.remove',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContentLocationRemoveData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
