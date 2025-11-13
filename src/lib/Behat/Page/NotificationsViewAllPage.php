<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Exception;
use Ibexa\AdminUi\Behat\Component\Dialog;
use Ibexa\AdminUi\Behat\Component\Table\TableBuilder;
use Ibexa\AdminUi\Behat\Component\Table\TableInterface;
use Ibexa\Behat\Browser\Element\Condition\ElementExistsCondition;
use Ibexa\Behat\Browser\Element\Criterion\ChildElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;

final class NotificationsViewAllPage extends Page
{
    private TableInterface $table;

    private Dialog $dialog;

    public function __construct(
        Session $session,
        Router $router,
        TableBuilder $tableBuilder,
        Dialog $dialog
    ) {
        parent::__construct($session, $router);

        $this->table = $tableBuilder->newTable()->build();
        $this->dialog = $dialog;
    }

    public function verifyNotificationIsOnList(string $notificationTitle): void
    {
        if (!$this->table->hasElement(['Title' => $notificationTitle])) {
            throw new Exception(sprintf('Notification "%s" not found on list.', $notificationTitle));
        }
    }

    public function markAsUnread(string $notificationTitle): void
    {
        $this->getHTMLPage()->setTimeout(10);
        $this->table->getTableRow(['Title' => $notificationTitle])->click($this->getLocator('markAsUnreadButton'));
    }

    public function goToContent(string $notificationTitle): void
    {
        $this->getHTMLPage()->setTimeout(5);
        $this->table->getTableRow(['Title' => $notificationTitle])->click($this->getLocator('goToContentButton'));
    }

    public function deleteNotification(string $notificationTitle): void
    {
        $this->table->getTableRow(['Title' => $notificationTitle])->click($this->getLocator('notificationCheckbox'));

        $this->getHTMLPage()->setTimeout(3)->find($this->getLocator('deleteButton'))->click();
        $this->dialog->verifyIsLoaded();
        $this->dialog->confirm();
    }

    public function getStatusForNotification(string $notificationTitle): string
    {
        return $this->getHTMLPage()->findAll($this->getLocator('tableRow'))
            ->getByCriterion(new ChildElementTextCriterion($this->getLocator('rowName'), $notificationTitle))
            ->find($this->getLocator('rowStatus'))->getText();
    }

    public function verifyNoNotificationsMessage(string $expectedMessage): void
    {
        $this->getHTMLPage()->setTimeout(10)->waitUntilCondition(
            new ElementExistsCondition(
                $this->getHTMLPage(),
                $this->getLocator('notificationsEmptyText')
            )
        );

        $this->getHTMLPage()->find($this->getLocator('notificationsEmptyText'))->assert()->textEquals($expectedMessage);
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->setTimeout(5)->find($this->getLocator('pageTitle'))->assert()->textContains('Notifications');
    }

    public function getName(): string
    {
        return 'Notifications';
    }

    protected function getRoute(): string
    {
        return '/notifications/render/all';
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('pageTitle', '.ibexa-page-title h1'),
            new VisibleCSSLocator('tableRow', 'tr'),
            new VisibleCSSLocator('rowName', '.ibexa-notification-view-all__details'),
            new VisibleCSSLocator('rowStatus', '.ibexa-notification-view-all__status'),
            new VisibleCSSLocator('markAsUnreadButton', '[data-original-title="Mark as unread"]'),
            new VisibleCSSLocator('goToContentButton', '[data-original-title="Go to content"]'),
            new VisibleCSSLocator('deleteButton', '.ibexa-notification-list__btn-delete'),
            new VisibleCSSLocator('notificationCheckbox', '.ibexa-notification-list__mark-row-checkbox'),
            new VisibleCSSLocator('notificationsEmptyText', '.ibexa-table__empty-table-info-text'),
        ];
    }
}
