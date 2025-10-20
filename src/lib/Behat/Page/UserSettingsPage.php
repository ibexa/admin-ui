<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

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

final class UserSettingsPage extends Page
{
    public function __construct(
        readonly Session $session,
        readonly Router $router,
        private readonly ContentActionsMenu $contentActionsMenu,
        private readonly TableNavigationTab $tableNavigationTab,
        private readonly IbexaDropdown $ibexaDropdown
    ) {
        parent::__construct($session, $router);
    }

    public function verifyIsLoaded(): void
    {
        $pageHeaderText = $this->getHTMLPage()->find($this->getLocator('title'))->getText();
        Assert::AssertContains($pageHeaderText, ['User settings', 'Content authoring', 'Browsing']);
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
            new VisibleCSSLocator('helpCenterValueDropdown', '#user_setting_update_help_center div.ibexa-dropdown__wrapper > ul'),
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

    public function openBrowsingEditionPage(): void
    {
        $this->getHTMLPage()
            ->findAll(new VisibleCSSLocator('settingsSection', '#ibexa-tab-my-preferences .ibexa-details'))
            ->getByCriterion(new ChildElementTextCriterion(new VisibleCSSLocator('settingHeader', '.ibexa-table-header__headline'), 'Browsing'))
            ->find(new VisibleCSSLocator('editButton', ' .ibexa-btn__label'))
            ->assert()->textEquals('Edit')
            ->click();
    }

    public function disableHelpCenter(): void
    {
        $this->contentActionsMenu->verifyIsLoaded();
        $this->getHTMLPage()->find($this->getLocator('helpCenterValueDropdown'))->click();
        $this->ibexaDropdown->selectOption('Disabled');
    }
}
