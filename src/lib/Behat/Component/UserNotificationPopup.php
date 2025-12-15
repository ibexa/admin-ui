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
use Ibexa\Behat\Browser\Element\Condition\ElementExistsCondition;
use Ibexa\Behat\Browser\Element\Condition\ElementNotExistsCondition;
use Ibexa\Behat\Browser\Element\Criterion\ChildElementTextCriterion;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Element\ElementInterface;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

final class UserNotificationPopup extends Component
{
    public function clickNotification(string $expectedType, string $expectedDescription): void
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

        throw new Exception(
            sprintf('Notification of type: %s with description: %d not found', $expectedType, $expectedDescription)
        );
    }

    public function verifyNotification(string $expectedType, string $expectedAuthor, string $expectedDescription, ?string $expectedDate = null, bool $shouldExist = true): void
    {
        $notifications = $this->getHTMLPage()->setTimeout(5)->findAll($this->getLocator('notificationItem'));

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
                    continue 2;
                }
            }

            if ($shouldExist) {
                return;
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

    public function openNotificationMenu(string $expectedDescription): void
    {
        $this->getHTMLPage()->setTimeout(5)->findAll($this->getLocator('notificationItem'))
            ->filterBy(new ChildElementTextCriterion($this->getLocator('notificationDescriptionText'), $expectedDescription))
            ->first()->find($this->getLocator('notificationMenuButton'))->click();

        $this->getHTMLPage()
            ->setTimeout(10)
            ->waitUntilCondition(
                new ElementExistsCondition(
                    $this->getHTMLPage(),
                    $this->getLocator('notificationActionsPopup'),
                )
            );
    }

    public function clickActionButton(string $buttonText): void
    {
        $this->getHTMLPage()
            ->setTimeout(10)
            ->findAll($this->getLocator('notificationMenuItemContent'))
            ->filterBy(new ElementTextCriterion($buttonText))->first()->execute(new MouseOverAndClick());

        $this->getHTMLPage()
            ->setTimeout(10)
            ->waitUntilCondition(
                new ElementNotExistsCondition(
                    $this->getHTMLPage(),
                    $this->getLocator('notificationActionsPopup')
                )
            );
    }

    public function findActionButton(string $buttonText): ElementInterface
    {
        $this->getHTMLPage()
            ->setTimeout(10)
            ->waitUntilCondition(
                new ElementExistsCondition(
                    $this->getHTMLPage(),
                    $this->getLocator('notificationMenuItemContent')
                )
            );

        return $this->getHTMLPage()
            ->setTimeout(10)
            ->findAll($this->getLocator('notificationMenuItemContent'))
            ->filterBy(new ElementTextCriterion($buttonText))
            ->first();
    }

    public function assertEmptyStateVisible(): void
    {
        $this->getHTMLPage()->setTimeout(5)->find($this->getLocator('notificationsEmptyText'))->assert()->isVisible();
    }

    public function clickOnMarkAllAsReadButton(): void
    {
        $this->getHTMLPage()->setTimeout(5)->find($this->getLocator('markAllAsReadButton'))->click();
    }

    public function verifyNotificationsCount(int $expectedCount): void
    {
        $this->getHTMLPage()->setTimeout(10)->find($this->getLocator('notificationsCount'))->assert()->textEquals('(' . $expectedCount . ')');
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
            new VisibleCSSLocator('markAllAsReadButton', '.ibexa-notifications-modal__mark-all-read-btn'),
            new VisibleCSSLocator('viewAllNotificationsButton', '.ibexa-notifications-modal__view-all-btn'),
            new VisibleCSSLocator('notificationActionsPopup', '.ibexa-notification-actions-popup-menu:not(.ibexa-popup-menu--hidden)'),
            new VisibleCSSLocator('notificationsEmptyText', '.ibexa-notifications-modal__empty-text'),
            new VisibleCSSLocator('notificationsCount', '.ibexa-notifications-modal__count'),
        ];
    }
}
