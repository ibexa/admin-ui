<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Notification;

class NotificationSelectionData
{
    private array $notifications;

    public function __construct(array $notifications = [])
    {
        $this->notifications = $notifications;
    }

    public function getNotifications(): array
    {
        return $this->notifications;
    }

    public function setNotifications(array $notifications): void
    {
        $this->notifications = $notifications;
    }
}
