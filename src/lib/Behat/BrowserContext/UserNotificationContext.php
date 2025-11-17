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
use Ibexa\AdminUi\Behat\Page\NotificationsViewAllPage;
use PHPUnit\Framework\Assert;

class UserNotificationContext implements Context
{
    /** @var \Ibexa\AdminUi\Behat\Component\UpperMenu */
    private $upperMenu;

    /** @var \Ibexa\AdminUi\Behat\Component\UserNotificationPopup */
    private $userNotificationPopup;

    private NotificationsViewAllPage $notificationsViewAllPage;

    public function __construct(UpperMenu $upperMenu, UserNotificationPopup $userNotificationPopup, NotificationsViewAllPage $notificationsViewAllPage)
    {
        $this->upperMenu = $upperMenu;
        $this->userNotificationPopup = $userNotificationPopup;
        $this->notificationsViewAllPage = $notificationsViewAllPage;
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
     * @When I open notification menu with description :description
     */
    public function iOpenNotificationMenu(string $description): void
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
     * @Then the notification action should be :expectedAction
     */
    public function verifyNotificationAction(string $expectedAction): void
    {
        $this->userNotificationPopup->getActionButton($expectedAction);
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
        $this->notificationsViewAllPage->verifyIsLoaded();
        $this->notificationsViewAllPage->verifyNotificationIsOnList($notificationTitle);
    }

    /**
     * @When I mark notification as unread with title :notificationTitle
     */
    public function iMarkNotificationAsUnread(string $notificationTitle): void
    {
        $this->notificationsViewAllPage->markAsUnread($notificationTitle);
    }

    /**
     * @Then the notification with title :notificationTitle has status :notificationStatus
     */
    public function verifyNotificationStatus(string $notificationTitle, string $notificationStatus): void
    {
        Assert::assertEquals($notificationStatus, $this->notificationsViewAllPage->getStatusForNotification($notificationTitle));
    }

    /**
     * @When I go to content of notification with title :notificationTitle
     */
    public function iGoToContent(string $notificationTitle): void
    {
        $this->notificationsViewAllPage->goToContent($notificationTitle);
    }

    /**
     * @When I delete notification with title :notificationTitle
     */
    public function iDeleteNotification(string $notificationTitle): void
    {
        $this->notificationsViewAllPage->deleteNotification($notificationTitle);
    }

    /**
     * @Then I should see the text :text in the notifications popup
     */
    public function iShouldSeeTextInNotificationsPopup(string $expectedMessage): void
    {
        $this->userNotificationPopup->verifyNoNotificationsMessage($expectedMessage);
    }

    /**
     * @Then I should see the text :text in the All Notifications view
     */
    public function iShouldSeeTextInAllNotificationsView(string $expectedMessage): void
    {
        $this->notificationsViewAllPage->verifyNoNotificationsMessage($expectedMessage);
    }
}
