<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Notification;

final class NotificationSelectionData
{
    /** @var bool[] notificationId => selected */
    private array $notifications;

    /**
     * @param bool[] $notifications
     */
    public function __construct(array $notifications = [])
    {
        $this->notifications = $notifications;
    }

    /**
     * @return bool[]
     */
    public function getNotifications(): array
    {
        return $this->notifications;
    }

    /**
     * @param bool[] $notifications
     */
    public function setNotifications(array $notifications): void
    {
        $this->notifications = $notifications;
    }
}
