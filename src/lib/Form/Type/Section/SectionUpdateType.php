<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Type\Section;

use Ibexa\AdminUi\Form\Data\Section\SectionUpdateData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectionUpdateType extends AbstractType
{
    public const BTN_UPDATE = 'update';

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
            ->add(self::BTN_UPDATE, SubmitType::class, [
                'label' => /** @Desc("Save") */
                    'section_update_form.update',
            ])
            ->add('update_and_edit', SubmitType::class, [
                'label' => /** @Desc("Save and edit") */
                    'section_create_form.update_and_edit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $this->sectionType->configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => SectionUpdateData::class,
            'translation_domain' => 'forms',
        ]);
    }
}

class_alias(SectionUpdateType::class, 'EzSystems\EzPlatformAdminUi\Form\Type\Section\SectionUpdateType');
