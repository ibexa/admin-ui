<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ContentTypeGroup;

use Ibexa\AdminUi\Form\Data\ContentTypeGroup\ContentTypeGroupsDeleteData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTypeGroupsDeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content_type_groups', CollectionType::class, [
                'entry_type' => CheckboxType::class,
                'required' => false,
                'allow_add' => true,
                'label' => false,
                'entry_options' => ['label' => false],
            ])
            ->add('delete', SubmitType::class, [
                'attr' => ['hidden' => true],
                'label' => /** @Desc("Delete content type groups") */ 'content_type_groups_delete_form.delete',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContentTypeGroupsDeleteData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
