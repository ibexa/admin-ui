<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Exception;
use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

class UserNotificationPopup extends Component
{
    public function clickNotification(string $expectedType, string $expectedDescription)
    {
        $notifications = $this->getHTMLPage()->findAll($this->getLocator('notificationItem'));

        foreach ($notifications as $notification) {
            $type = $notification->find($this->getLocator('notificationType'))->getText();
            if ($type !== $expectedType) {
                continue;
            }

            $notificationTitle = $this->getHTMLPage()->setTimeout(3)->find($this->getLocator('notificationDescriptionTitle'))->getText();
            $notificationText = $this->getHTMLPage()->setTimeout(3)->find($this->getLocator('notificationDescriptionText'))->getText();

            $description = sprintf('%s %s', $notificationTitle, $notificationText);

            if ($description !== $expectedDescription) {
                continue;
            }

            $notification->click();

            return;
        }

        throw new Exception(sprintf('Notification of type: %s with description: %d not found', $expectedType, $expectedDescription));
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()
            ->setTimeout(5)
            ->find($this->getLocator('notificationsPopupTitle'))
            ->assert()->textContains('Notifications');
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('notificationsPopupTitle', '.ibexa-side-panel__header'),
            new VisibleCSSLocator('notificationItem', '.ibexa-notifications-modal__item'),
            new VisibleCSSLocator('notificationType', '.ibexa-notifications-modal__type-content > strong > span'),
            new VisibleCSSLocator('notificationDescriptionTitle', '.ibexa-notifications-modal__type-content > p.description__title'),
            new VisibleCSSLocator('notificationDescriptionText', '.ibexa-notifications-modal__type-content > p.description__text'),
        ];
    }
}
