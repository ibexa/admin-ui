<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Role;

use Ibexa\AdminUi\Form\Data\Role\RoleAssignmentCreateData;
use Ibexa\AdminUi\Form\Type\Content\LocationType;
use Ibexa\AdminUi\Form\Type\Section\SectionChoiceType;
use Ibexa\AdminUi\Form\Type\User\UserCollectionType;
use Ibexa\AdminUi\Form\Type\User\UserGroupCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleAssignmentCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'groups',
                UserGroupCollectionType::class,
                [
                    'label' => /** @Desc("Group") */ 'role_assignment.groups',
                ]
            )
            ->add(
                'users',
                UserCollectionType::class,
                [
                    'label' => /** @Desc("User") */ 'role_assignment.users',
                ]
            )
            ->add(
                'sections',
                SectionChoiceType::class,
                [
                    'required' => false,
                    'multiple' => true,
                    'label' => /** @Desc("Section") */ 'role_assignment.sections',
                ]
            )
            ->add(
                'locations',
                LocationType::class,
                [
                    'required' => false,
                    'multiple' => true,
                    'label' => /** @Desc("Select subtree") */ 'role_assignment.locations',
                ]
            )
            ->add('limitation_type', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'choices' => [
                    RoleAssignmentCreateData::LIMITATION_TYPE_NONE => RoleAssignmentCreateData::LIMITATION_TYPE_NONE,
                    RoleAssignmentCreateData::LIMITATION_TYPE_SECTION => RoleAssignmentCreateData::LIMITATION_TYPE_SECTION,
                    RoleAssignmentCreateData::LIMITATION_TYPE_LOCATION => RoleAssignmentCreateData::LIMITATION_TYPE_LOCATION,
                ],
                'choice_name' => static function ($value) {
                    return $value;
                },
            ])
            ->add(
                'save',
                SubmitType::class,
                ['label' => /** @Desc("Assign") */ 'role_assignment.save']
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RoleAssignmentCreateData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
