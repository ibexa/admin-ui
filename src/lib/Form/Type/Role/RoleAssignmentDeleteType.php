<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Role;

use Ibexa\AdminUi\Form\Data\Role\RoleAssignmentDeleteData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleAssignmentDeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'role_assignment',
                RoleAssignmentType::class,
                ['label' => false]
            )
            ->add(
                'delete',
                SubmitType::class,
                ['label' => /** @Desc("Delete") */ 'role_assignment.delete']
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RoleAssignmentDeleteData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
