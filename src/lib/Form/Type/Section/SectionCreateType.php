<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Section;

use Ibexa\AdminUi\Form\Data\Section\SectionCreateData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<\Ibexa\AdminUi\Form\Data\Section\SectionCreateData>
 */
final class SectionCreateType extends AbstractType
{
    public function __construct(private readonly SectionType $sectionType)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->sectionType->buildForm($builder, $options);

        $builder
            ->add('create', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $this->sectionType->configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => SectionCreateData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
