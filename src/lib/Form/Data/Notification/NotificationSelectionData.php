<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Notification;

final class NotificationSelectionData
{
    /** @var bool[] */
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

    /**
     * Pomocnicza metoda do stworzenia obiektu z tablicy powiadomień.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Notification\Notification[] $notifications
     */
    public static function fromNotificationObjects(array $notifications): self
    {
        $ids = [];
        foreach ($notifications as $notification) {
            $ids[$notification->id] = false; // domyślnie false - niezaznaczone
        }

        return new self($ids);
    }
}
