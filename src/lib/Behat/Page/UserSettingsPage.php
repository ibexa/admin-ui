<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\ContentActionsMenu;
use Ibexa\AdminUi\Behat\Component\TableNavigationTab;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;

class UserSettingsPage extends Page
{
    private ContentActionsMenu $contentActionsMenu;

    private TableNavigationTab $tableNavigationTab;

    public function __construct(Session $session, Router $router, ContentActionsMenu $contentActionsMenu, TableNavigationTab $tableNavigationTab)
    {
        parent::__construct($session, $router);
        $this->contentActionsMenu = $contentActionsMenu;
        $this->tableNavigationTab = $tableNavigationTab;
    }

    public function verifyIsLoaded(): void
    {
        $this->contentActionsMenu->verifyIsLoaded();
        $this->getHTMLPage()->find($this->getLocator('title'))->assert()->textEquals('User Settings');
    }

    public function switchTab(string $tabName): void
    {
        $this->tableNavigationTab->goToTab($tabName);
    }

    public function changePassword(): void
    {
        $this->getHTMLPage()
            ->findAll($this->getLocator('button'))
            ->getByCriterion(new ElementTextCriterion('Change password'))
            ->click();
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('button', '.ibexa-btn'),
        ];
    }

    protected function getRoute(): string
    {
        return '/user/settings/list';
    }

    public function getName(): string
    {
        return 'User Settings';
    }
}
