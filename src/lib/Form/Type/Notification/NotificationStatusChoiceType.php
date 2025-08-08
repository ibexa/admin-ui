<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Notification;

use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<mixed>
 */
final class NotificationStatusChoiceType extends AbstractType
{
    public const NOTIFICATION_STATUS_READ = 0;
    public const NOTIFICATION_STATUS_UNREAD = 1;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [
                /** @Desc("Read") */
                'notification.status.read' => self::NOTIFICATION_STATUS_READ,
                /** @Desc("Unread") */
                'notification.status.unread' => self::NOTIFICATION_STATUS_UNREAD,
            ],
            'translation_domain' => 'ibexa_notifications',
            'choice_translation_domain' => 'ibexa_notifications',
            'label' => /** @Desc("Status") */ 'notification.status',
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'notification_status_choice';
    }
}
