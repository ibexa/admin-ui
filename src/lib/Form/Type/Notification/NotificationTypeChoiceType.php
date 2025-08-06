<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Notification;

use Ibexa\Core\Notification\Renderer\Registry;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends \Symfony\Component\Form\AbstractType<mixed>
 */
final class NotificationTypeChoiceType extends AbstractType
{
    private Registry $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $typeLabels = $this->registry->getTypeLabels() ?: [];

        $choices = array_flip($typeLabels);

        $resolver->setDefaults([
            'choices' => $choices,
            'required' => false,
            'placeholder' => /** @Desc("All types") */ 'notification.all_types',
            'translation_domain' => 'ibexa_notifications',
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
