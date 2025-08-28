<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\EventListener;

use Symfony\Component\Form\FormEvent;

final readonly class SelectionMultilingualOptionsDataListener
{
    public function __construct(private string $languageCode)
    {
    }

    public function setLanguageOptions(FormEvent $event): void
    {
        $data = $event->getData();
        $event->setData($data[$this->languageCode] ?? []);
    }
}
