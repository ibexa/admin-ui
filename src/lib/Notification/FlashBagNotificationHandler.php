<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Notification;

use Ibexa\Contracts\AdminUi\Notification\NotificationHandlerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

final class FlashBagNotificationHandler implements NotificationHandlerInterface
{
    private const TYPE_INFO = 'info';
    private const TYPE_SUCCESS = 'success';
    private const TYPE_WARNING = 'warning';
    private const TYPE_ERROR = 'error';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function info(string $message): void
    {
        $this->getFlashBag()->add(self::TYPE_INFO, $message);
    }

    public function success(string $message): void
    {
        $this->getFlashBag()->add(self::TYPE_SUCCESS, $message);
    }

    public function warning(string $message): void
    {
        $this->getFlashBag()->add(self::TYPE_WARNING, $message);
    }

    public function error(string $message): void
    {
        $this->getFlashBag()->add(self::TYPE_ERROR, $message);
    }

    private function getFlashBag(): FlashBagInterface
    {
        return $this->requestStack->getSession()->getFlashBag();
    }
}
