<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Ibexa\AdminUi\Behat\Component\UpperMenu;
use Ibexa\AdminUi\Behat\Component\UserNotificationPopup;
use Ibexa\AdminUi\Behat\Page\NotificationsPage;
use PHPUnit\Framework\Assert;

class UserNotificationContext implements Context
{
    /** @var \Ibexa\AdminUi\Behat\Component\UpperMenu */
    private $upperMenu;

    /** @var \Ibexa\AdminUi\Behat\Component\UserNotificationPopup */
    private $userNotificationPopup;

    private NotificationsPage $notificationsPage;

    private int $previousCount;

    public function __construct(UpperMenu $upperMenu, UserNotificationPopup $userNotificationPopup, NotificationsPage $notificationsPage)
    {
        $this->upperMenu = $upperMenu;
        $this->userNotificationPopup = $userNotificationPopup;
        $this->notificationsPage = $notificationsPage;
    }

    /**
     * @Given there is an unread notification for current user
     */
    public function thereIsNotificationForCurrentUser()
    {
        Assert::assertTrue($this->upperMenu->hasUnreadNotification());
    }

    /**
     * @Given I go to user notification with details:
     */
    public function iGoToUserNotificationWithDetails(TableNode $notificationDetails)
    {
        $type = $notificationDetails->getHash()[0]['Type'];
        $description = $notificationDetails->getHash()[0]['Description'];

        $this->userNotificationPopup->verifyIsLoaded();
        $this->userNotificationPopup->clickNotification($type, $description);
    }

    /**
     * @Then the notification appears with details:
     */
    public function thereIsNotificationAppearsWithDetails(TableNode $notificationDetails): void
    {
        $type = $notificationDetails->getHash()[0]['Type'];
        $author = $notificationDetails->getHash()[0]['Author'];
        $description = $notificationDetails->getHash()[0]['Description'];
        $date = $notificationDetails->getHash()[0]['Date'];

        $this->userNotificationPopup->verifyIsLoaded();
        $this->userNotificationPopup->verifyNotification($type, $author, $description, $date, true);
    }

    /**
     * @Then no notification appears with details:
     */
    public function noNotificationAppearsWithDetails(TableNode $notificationDetails): void
    {
        $type = $notificationDetails->getHash()[0]['Type'];
        $author = $notificationDetails->getHash()[0]['Author'];
        $description = $notificationDetails->getHash()[0]['Description'];
        $date = $notificationDetails->getHash()[0]['Date'];

        $this->userNotificationPopup->verifyIsLoaded();
        $this->userNotificationPopup->verifyNotification($type, $author, $description, $date, false);
    }

    /**
     * @When I open notification menu with description :description
     */
    public function iOpenNotificationMenuNotification(string $description): void
    {
        $this->userNotificationPopup->openNotificationMenu($description);
    }

    /**
     * @When I click :action action
     */
    public function iClickActionButton(string $action): void
    {
        $this->userNotificationPopup->clickActionButton($action);
    }

    /**
     * @When I store current notification count
     */
    public function storeNotificationCount(): void
    {
        $this->userNotificationPopup->verifyIsLoaded();
        $this->previousCount = $this->userNotificationPopup->getNotificationCount();
    }

    /**
     * @Then the notification count should change in :direction direction
     */
    public function verifyNotificationCountChanged(string $direction): void
    {
        $this->userNotificationPopup->verifyNotificationCountChanged($this->previousCount, $direction);
    }

    /**
     * @When I click mark all as read button
     */
    public function iClickButton(): void
    {
        $this->userNotificationPopup->verifyIsLoaded();
        $this->userNotificationPopup->clickOnMarkAllAsReadButton();
    }

    /**
     * @When I click view all notifications button
     */
    public function iClickViewAllNotificationsButton(): void
    {
        $this->userNotificationPopup->verifyIsLoaded();
        $this->userNotificationPopup->clickViewAllNotificationsButton();
    }

    /**
     * @Then there is :notificationTitle notification on list
     */
    public function thereIsNotificationOnList(string $notificationTitle): void
    {
        $this->notificationsPage->verifyIsLoaded();
        $this->notificationsPage->verifyNotificationIsOnList($notificationTitle);
    }

    /**
     * @Then there is no :notificationTitle notification on list
     */
    public function thereIsNoNotificationOnList(string $notificationTitle): void
    {
        $this->notificationsPage->verifyIsLoaded();
        $this->notificationsPage->verifyNotificationIsNotOnList($notificationTitle);
    }

    /**
     * @When I marked as unread notification with title :notificationTitle
     */
    public function iMarkedNotificationAsUnread(string $notificationTitle): void
    {
        $this->notificationsPage->markAsUnread($notificationTitle);
    }

    /**
     * @Then the notification with title :notificationTitle has status :notificationStatus
     */
    public function verifyNotificationStatus(string $notificationTitle, string $notificationStatus): void
    {
        Assert::assertEquals($notificationStatus, $this->notificationsPage->getStatusForNotification($notificationTitle));
    }

    /**
     * @When I go to content of notification with title :notificationTitle
     */
    public function iGoToContent(string $notificationTitle): void
    {
        $this->notificationsPage->goToContent($notificationTitle);
    }

    /**
     * @When I deleted notification with title :notificationTitle
     */
    public function iDeleteNotification(string $notificationTitle): void
    {
        $this->notificationsPage->deleteNotification($notificationTitle);
    }
}
