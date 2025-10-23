<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Exception;
use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\Action\MouseOverAndClick;
use Ibexa\Behat\Browser\Element\Criterion\ChildElementTextCriterion;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
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

    public function verifyNotification(string $expectedType, string $expectedAuthor, string $expectedDescription, ?string $expectedDate = null, bool $shouldExist = true): void
    {
        $notifications = $this->getHTMLPage()->setTimeout(3)->findAll($this->getLocator('notificationItem'));

        foreach ($notifications as $notification) {
            $criteria = [
                new ChildElementTextCriterion($this->getLocator('notificationType'), $expectedType),
                new ChildElementTextCriterion($this->getLocator('notificationDescriptionTitle'), $expectedAuthor),
                new ChildElementTextCriterion($this->getLocator('notificationDescriptionText'), $expectedDescription),
            ];

            if ($expectedDate !== null && $expectedDate !== 'XXXX-XX-XX') {
                $criteria[] = new ChildElementTextCriterion($this->getLocator('notificationDate'), $expectedDate);
            }

            foreach ($criteria as $criterion) {
                if (!$criterion->matches($notification)) {
                    continue 2; // go to next notification
                }
            }

            if ($shouldExist) {
                return; // matching notification found
            } else {
                throw new \Exception(sprintf(
                    'Notification of type "%s" with author "%s" and description "%s" should not exist, but was found.',
                    $expectedType,
                    $expectedAuthor,
                    $expectedDescription
                ));
            }
        }

        if ($shouldExist) {
            throw new \Exception(sprintf(
                'Notification of type "%s" with author "%s" and description "%s" was not found.',
                $expectedType,
                $expectedAuthor,
                $expectedDescription
            ));
        }
    }

    public function getNotificationCount(): int
    {
        try {
            $counterElement = $this->getHTMLPage()->find($this->getLocator('notificationCounterDot'));
            $countText = $counterElement->getAttribute('data-count');

            return (int) $countText;
        } catch (Exception $e) {
            return 0;
        }
    }

    public function verifyNotificationCountChanged(int $previousCount, string $direction): void
    {
        $attempts = 10;
        $interval = 500000;
        $currentCount = 0;

        for ($i = 0; $i < $attempts; ++$i) {
            $currentCount = $this->getNotificationCount();

            if (($direction === 'increase' && $currentCount > $previousCount) ||
                ($direction === 'decrease' && $currentCount < $previousCount)) {
                return;
            }

            usleep($interval);
        }

        throw new \Exception(sprintf(
            'Expected notification count to %s (previous: %d, current: %d)',
            $direction,
            $previousCount,
            $currentCount
        ));
    }

    public function openNotificationMenu(string $expectedDescription): void
    {
        $notifications = $this->getHTMLPage()
            ->setTimeout(5)
            ->findAll($this->getLocator('notificationItem'))
            ->filterBy(new ChildElementTextCriterion(
                $this->getLocator('notificationDescriptionText'),
                $expectedDescription
            ));

        $menuButton = $notifications->first()->find($this->getLocator('notificationMenuButton'));
        $menuButton->click();
    }

    public function clickActionButton(string $buttonText): void
    {
        $buttons = $this->getHTMLPage()
            ->setTimeout(5)
            ->findAll($this->getLocator('notificationMenuItemContent'))
            ->filterBy(new ElementTextCriterion($buttonText));

        $buttons->first()->execute(new MouseOverAndClick());
    }

    public function clickOnMarkAllAsReadButton(): void
    {
        $this->getHTMLPage()->setTimeout(5)->find($this->getLocator('markAllAsReadButton'))->click();
    }

    public function clickViewAllNotificationsButton(): void
    {
        $this->getHTMLPage()->setTimeout(3)->find($this->getLocator('viewAllNotificationsButton'))->click();
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
            new VisibleCSSLocator('notificationType', '.ibexa-notifications-modal__type-content .type__text'),
            new VisibleCSSLocator('notificationDescriptionTitle', '.ibexa-notifications-modal__description .description__title'),
            new VisibleCSSLocator('notificationDescriptionText', '.ibexa-notifications-modal__type-content .description__text'),
            new VisibleCSSLocator('notificationDate', '.ibexa-notifications-modal__item--date'),
            new VisibleCSSLocator('notificationMenuButton', '.ibexa-notifications-modal__actions'),
            new VisibleCSSLocator('notificationMenuItemContent', '.ibexa-popup-menu__item-content.ibexa-multilevel-popup-menu__item-content'),
            new VisibleCSSLocator('notificationCounterDot', '.ibexa-header-user-menu__notice-dot'),
            new VisibleCSSLocator('markAllAsReadButton', '.ibexa-notifications-modal__mark-all-read-btn'),
            new VisibleCSSLocator('viewAllNotificationsButton', '.ibexa-notifications-modal__view-all-btn'),
        ];
    }
}
