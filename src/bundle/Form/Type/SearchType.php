<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Form\Type;

use Ibexa\AdminUi\Form\Type\DateRangeType;
use Ibexa\Bundle\AdminUi\Form\Data\SearchQueryData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'required' => false,
                'choices' => array_combine($options['notification_types'], $options['notification_types']),
                'placeholder' => 'All types',
                'label' => 'Type',
            ])
            ->add('statuses', ChoiceType::class, [
                'choices' => [
                    'Read' => 'read',
                    'Unread' => 'unread',
                ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'label' => 'Status',
            ])
            ->add('createdRange', DateRangeType::class, [
                'required' => false,
                'label' => 'Date and time',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'data_class' => SearchQueryData::class,
            'notification_types' => [],
        ]);
    }
}
