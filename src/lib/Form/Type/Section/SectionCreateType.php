<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Type\Section;

use Ibexa\AdminUi\Form\Data\Section\SectionCreateData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectionCreateType extends AbstractType
{
    public const BTN_CREATE_AND_EDIT = 'create_and_edit';

    /** @var SectionType */
    protected $sectionType;

    /**
     * @param SectionType $sectionType
     */
    public function __construct(SectionType $sectionType)
    {
        $this->sectionType = $sectionType;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->sectionType->buildForm($builder, $options);

        $builder
            ->add('create', SubmitType::class, [
                'label' => /** @Desc("Create") */
                    'section_create_form.create',
            ])
            ->add(self::BTN_CREATE_AND_EDIT, SubmitType::class, [
                'label' => /** @Desc("Save and edit") */
                    'section_create_form.create_and_edit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $this->sectionType->configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => SectionCreateData::class,
            'translation_domain' => 'forms',
        ]);
    }
}

class_alias(SectionCreateType::class, 'EzSystems\EzPlatformAdminUi\Form\Type\Section\SectionCreateType');
