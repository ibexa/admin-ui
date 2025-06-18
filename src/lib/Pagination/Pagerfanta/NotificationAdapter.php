<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\NotificationService;
use Ibexa\Contracts\Core\Repository\Values\Notification\NotificationList;
use Ibexa\Contracts\Core\Repository\Values\Notification\Query\Criterion\NotificationQuery;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Pagerfanta adapter for Ibexa content search.
 * Will return results as notification list.
 */
class NotificationAdapter implements AdapterInterface
{
    private NotificationService $notificationService;

    private NotificationQuery $query;

    private int $nbResults;

    public function __construct(
        NotificationService $notificationService,
        NotificationQuery $query
    ) {
        $this->notificationService = $notificationService;
        $this->query = $query;
    }

    /**
     * Returns the number of results.
     *
     * @return int the number of results
     */
    public function getNbResults(): int
    {
        return $this->nbResults ?? ($this->nbResults = $this->notificationService->getNotificationCount($this->query));
    }

    /**
     * Returns a slice of the results.
     *
     * @param int $offset the offset
     * @param int $length the length
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Notification\NotificationList
     */
    public function getSlice($offset, $length): NotificationList
    {
        $notifications = $this->notificationService->findNotifications($this->query);

        $this->nbResults ??= $notifications->totalCount;

        return $notifications;
    }
}

class_alias(NotificationAdapter::class, 'EzSystems\EzPlatformAdminUi\Pagination\Pagerfanta\NotificationAdapter');
