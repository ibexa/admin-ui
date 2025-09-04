<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\NotificationService;
use Ibexa\Contracts\Core\Repository\Values\Notification\NotificationList;
use Ibexa\Contracts\Core\Repository\Values\Notification\Query\NotificationQuery;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @implements \Pagerfanta\Adapter\AdapterInterface<\Ibexa\Contracts\Core\Repository\Values\Notification\Notification>
 */
class NotificationAdapter implements AdapterInterface
{
    /** @phpstan-var int<0, max> */
    private int $nbResults;

    /**
     * @param \Ibexa\Contracts\Core\Repository\NotificationService $notificationService
     */
    public function __construct(
        private NotificationService $notificationService,
        private NotificationQuery $query
    ) {
    }

    public function getNbResults(): int
    {
        if (isset($this->nbResults)) {
            return $this->nbResults;
        }

        $query = clone $this->query;
        $query->setOffset(0);
        $query->setLimit(0);

        return $this->nbResults = $this->notificationService->getNotificationCount($query);
    }

    public function getSlice(int $offset, int $length): NotificationList
    {
        $query = clone $this->query;
        $query->setOffset($offset);
        $query->setLimit($length);
        $notifications = $this->notificationService->findNotifications($query);

        $this->nbResults ??= $notifications->totalCount;

        return $notifications;
    }
}
