<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Section;

use Ibexa\AdminUi\Form\Data\Section\SectionDeleteData;
use Ibexa\AdminUi\Form\Type\Embedded\SectionType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<\Ibexa\AdminUi\Form\Data\Section\SectionDeleteData>
 */
final class SectionDeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'section',
                SectionType::class,
                ['label' => false, 'attr' => ['hidden' => true]]
            )
            ->add(
                'delete',
                SubmitType::class,
                [
                    'label' => /** @Desc("Delete") */
                        'section_delete_form.delete',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SectionDeleteData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
