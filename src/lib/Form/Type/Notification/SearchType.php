<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Notification;

use Ibexa\AdminUi\Form\Type\DateRangeType;
use Ibexa\Bundle\AdminUi\Form\Data\SearchQueryData;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<\Ibexa\Bundle\AdminUi\Form\Data\SearchQueryData>
 */
final class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', NotificationTypeChoiceType::class, [
                'required' => false,
            ])
            ->add('statuses', NotificationStatusChoiceType::class, [
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ])
            ->add('createdRange', DateRangeType::class, [
                'required' => false,
                'label' => /** @Desc("Date and time") */ 'notification.date_and_time',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchQueryData::class,
            'translation_domain' => 'ibexa_notifications',
        ]);
    }
}
