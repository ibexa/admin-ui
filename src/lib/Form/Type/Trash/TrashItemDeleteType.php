<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Trash;

use Ibexa\AdminUi\Form\Data\Trash\TrashItemDeleteData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrashItemDeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('trash_items', CollectionType::class, [
            'entry_type' => TrashItemCheckboxType::class,
            'entry_options' => [
                'required' => false,
                'attr' => ['hidden' => true],
            ],
            'label' => false,
            'allow_add' => true,
        ]);

        $builder->add('delete', SubmitType::class, [
            'label' => false,
            'attr' => ['hidden' => true],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TrashItemDeleteData::class,
            'translation_domain' => 'forms',
        ]);
    }
}
