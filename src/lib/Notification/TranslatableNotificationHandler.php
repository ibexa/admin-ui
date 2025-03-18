<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Notification;

use Ibexa\Contracts\AdminUi\Notification\NotificationHandlerInterface;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\User\ExceptionHandler\ActionResultHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TranslatableNotificationHandler implements TranslatableNotificationHandlerInterface, ActionResultHandler
{
    private NotificationHandlerInterface $notificationHandler;

    private TranslatorInterface $translator;

    public function __construct(
        NotificationHandlerInterface $notificationHandler,
        TranslatorInterface $translator
    ) {
        $this->notificationHandler = $notificationHandler;
        $this->translator = $translator;
    }

    public function info(string $message, array $parameters = [], ?string $domain = null, ?string $locale = null): void
    {
        $translatedMessage = $this->translator->trans(
            /** @Ignore */
            $message,
            $parameters,
            $domain,
            $locale
        );
        $this->notificationHandler->info(/** @Ignore */
            $translatedMessage
        );
    }

    public function success(string $message, array $parameters = [], ?string $domain = null, ?string $locale = null): void
    {
        $translatedMessage = $this->translator->trans(
            /** @Ignore */
            $message,
            $parameters,
            $domain,
            $locale
        );
        $this->notificationHandler->success(/** @Ignore */
            $translatedMessage
        );
    }

    public function warning(string $message, array $parameters = [], ?string $domain = null, ?string $locale = null): void
    {
        $translatedMessage = $this->translator->trans(
            /** @Ignore */
            $message,
            $parameters,
            $domain,
            $locale
        );
        $this->notificationHandler->warning(/** @Ignore */
            $translatedMessage
        );
    }

    public function error(string $message, array $parameters = [], ?string $domain = null, ?string $locale = null): void
    {
        $translatedMessage = $this->translator->trans(
            /** @Ignore */
            $message,
            $parameters,
            $domain,
            $locale
        );
        $this->notificationHandler->error(/** @Ignore */
            $translatedMessage
        );
    }
}
