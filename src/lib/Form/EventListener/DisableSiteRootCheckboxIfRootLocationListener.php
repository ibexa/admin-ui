<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\EventListener;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvent;

final readonly class DisableSiteRootCheckboxIfRootLocationListener
{
    public function onPreSetData(FormEvent $event): void
    {
        $location = $event->getData()->getLocation();
        if (null === $location || 1 >= $location->depth) {
            return;
        }

        $form = $event->getForm();
        $form->add(
            'site_root',
            CheckboxType::class,
            [
                'required' => false,
                'label' => false,
                'disabled' => true,
            ]
        );
    }
}
