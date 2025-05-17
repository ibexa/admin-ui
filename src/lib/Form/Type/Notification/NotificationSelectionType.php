<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Notification;

use Ibexa\AdminUi\Form\Data\Notification\NotificationSelectionData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotificationSelectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'notifications',
            CollectionType::class,
            [
                'entry_type' => CheckboxType::class,
                'required' => false,
                'allow_add' => true,
                'entry_options' => ['label' => false],
                'label' => false,
            ]
        );

        $builder->add(
            $options['submit_name'],
            SubmitType::class
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NotificationSelectionData::class,
            'submit_name' => 'submit',
        ]);
    }
}
