<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Notification;

use Ibexa\AdminUi\Form\Type\DateRangeType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<mixed>
 */
final class NotificationCreatedRangeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'label' => /** @Desc("Date and time") */ 'notification.date_and_time',
            'translation_domain' => 'ibexa_notifications',
        ]);
    }

    public function getParent(): string
    {
        return DateRangeType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'notification_created_range';
    }
}
