<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ObjectState;

use Ibexa\AdminUi\Form\Data\ObjectState\ObjectStateGroupCreateData;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectStateGroupCreateType extends AbstractType
{
    public const BTN_CREATE_AND_EDIT = 'create_and_edit';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('identifier', TextType::class, [
                'label' => /** @Desc("Identifier") */ 'object_state_group.create.identifier',
            ])
            ->add('name', TextType::class, [
                'label' => /** @Desc("Name") */ 'object_state_group.create.name',
            ])
            ->add('create', SubmitType::class, [
                'label' => /** @Desc("Save and close") */ 'object_state_group.create.create',
            ])
            ->add(self::BTN_CREATE_AND_EDIT, SubmitType::class, [
                'label' => /** @Desc("Save") */ 'object_state_group.create.create_and_edit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ObjectStateGroupCreateData::class,
            'translation_domain' => 'ibexa_object_state',
        ]);
    }
}
