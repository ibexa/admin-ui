<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\NotificationService;
use Ibexa\Contracts\Core\Repository\Values\Notification\NotificationList;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @implements \Pagerfanta\Adapter\AdapterInterface<\Ibexa\Contracts\Core\Repository\Values\Notification\Notification>
 */
class NotificationAdapter implements AdapterInterface
{
    private NotificationService $notificationService;

    /** @phpstan-var int<0, max> */
    private int $nbResults;

    /**
     * @param \Ibexa\Contracts\Core\Repository\NotificationService $notificationService
     */
    public function __construct(
        NotificationService $notificationService
    ) {
        $this->notificationService = $notificationService;
    }

    public function getNbResults(): int
    {
        return $this->nbResults ?? ($this->nbResults = $this->notificationService->getNotificationCount());
    }

    public function getSlice(int $offset, int $length): NotificationList
    {
        $notifications = $this->notificationService->loadNotifications($offset, $length);

        if (!isset($this->nbResults)) {
            $this->nbResults = $notifications->totalCount;
        }

        return $notifications;
    }
}
