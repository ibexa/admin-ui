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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends \Symfony\Component\Form\AbstractType<\Ibexa\Bundle\AdminUi\Form\Data\SearchQueryData>
 */
final class SearchType extends AbstractType
{
    public const NOTIFICATION_STATUS_READ = 0;
    public const NOTIFICATION_STATUS_UNREAD = 1;

    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $notificationTypeChoices = array_flip($options['notification_types']);
        $statusChoices = [
            /** @Desc("Read") */
            $this->translator->trans('notification.status.read') => self::NOTIFICATION_STATUS_READ,
            /** @Desc("Unread") */
            $this->translator->trans('notification.status.unread') => self::NOTIFICATION_STATUS_UNREAD,
        ];

        $builder
            ->add('type', ChoiceType::class, [
                'required' => false,
                'choices' => $notificationTypeChoices,
                'placeholder' => /** @Desc("All types") */ 'notification.all_types',
            ])
            ->add('statuses', ChoiceType::class, [
                'choices' => $statusChoices,
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'label' => /** @Desc("Status") */ 'notification.status',
            ])
            ->add('createdRange', DateRangeType::class, [
                'required' => false,
                'label' => /** @Desc("Date and time") */ 'notification.date_and_time',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'csrf_protection' => false,
            'data_class' => SearchQueryData::class,
            'notification_types' => [],
            'translation_domain' => 'ibexa_notifications',
        ]);
    }
}
