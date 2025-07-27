<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\ContentActionsMenu;
use Ibexa\AdminUi\Behat\Component\IbexaDropdown;
use Ibexa\AdminUi\Behat\Component\TableNavigationTab;
use Ibexa\Behat\Browser\Element\Criterion\ChildElementTextCriterion;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;
use PHPUnit\Framework\Assert;

class UserSettingsPage extends Page
{
    private ContentActionsMenu $contentActionsMenu;

    private TableNavigationTab $tableNavigationTab;

    private IbexaDropdown $ibexaDropdown;

    public function __construct(Session $session, Router $router, ContentActionsMenu $contentActionsMenu, TableNavigationTab $tableNavigationTab, IbexaDropdown $ibexaDropdown)
    {
        parent::__construct($session, $router);
        $this->contentActionsMenu = $contentActionsMenu;
        $this->tableNavigationTab = $tableNavigationTab;
        $this->ibexaDropdown = $ibexaDropdown;
    }

    public function verifyIsLoaded(): void
    {
        $pageHeaderText = $this->getHTMLPage()->find($this->getLocator('title'))->getText();
        Assert::AssertContains($pageHeaderText, ['User settings', 'Content authoring']);
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
            new VisibleCSSLocator('title', '.ibexa-edit-header__title,.ibexa-page-title__content'),
            new VisibleCSSLocator('autosaveDraftValueDropdown', '#user_setting_update_autosave div.ibexa-dropdown__wrapper > ul'),
            new VisibleCSSLocator('autosaveIntervalEdit', '#user_setting_update_autosave_interval_value'),
        ];
    }

    public function openAutosaveDraftEditionPage(): void
    {
        $this->getHTMLPage()
            ->findAll(new VisibleCSSLocator('settingsSection', '#ibexa-tab-my-preferences .ibexa-details'))
            ->getByCriterion(new ChildElementTextCriterion(new VisibleCSSLocator('settingHeader', '.ibexa-table-header__headline'), 'Content authoring'))
            ->find(new VisibleCSSLocator('editButton', ' .ibexa-btn__label'))
            ->assert()->textEquals('Edit')
            ->click();
    }

    public function openAutosaveDraftIntervalEditionPage(): void
    {
        $this->getHTMLPage()->find($this->getLocator('autosaveIntervalEdit'))->click();
    }

    public function disableAutosave(): void
    {
        $this->contentActionsMenu->verifyIsLoaded();
        $this->getHTMLPage()->find($this->getLocator('autosaveDraftValueDropdown'))->click();
        $this->ibexaDropdown->selectOption('Disabled');
    }

    protected function getRoute(): string
    {
        return '/user/settings/list';
    }

    public function getName(): string
    {
        return 'User settings';
    }
}
