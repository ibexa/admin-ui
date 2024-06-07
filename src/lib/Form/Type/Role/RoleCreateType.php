<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Role;

use Ibexa\AdminUi\Form\Data\Role\RoleCreateData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleCreateType extends AbstractType
{
    public const BTN_SAVE = 'save';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'identifier',
                TextType::class,
                ['label' => /** @Desc("Name") */ 'role_create.name']
            )
            ->add(
                self::BTN_SAVE,
                SubmitType::class,
                ['label' => /** @Desc("Save") */ 'role_create.save']
            )
            ->add(
                'save_and_close',
                SubmitType::class,
                ['label' => /** @Desc("Save and close") */ 'role_create.save_and_close']
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RoleCreateData::class,
            'translation_domain' => 'forms',
        ]);
    }
}

class_alias(RoleCreateType::class, 'EzSystems\EzPlatformAdminUi\Form\Type\Role\RoleCreateType');
