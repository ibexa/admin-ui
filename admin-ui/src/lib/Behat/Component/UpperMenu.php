<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Behat\Mink\Session;
use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

class UpperMenu extends Component
{
    private IbexaDropdown $ibexaDropdown;

    public function __construct(Session $session, IbexaDropdown $ibexaDropdown)
    {
        parent::__construct($session);
        $this->ibexaDropdown = $ibexaDropdown;
    }

    public function goToDashboard(): void
    {
        $this->getHTMLPage()->find($this->getLocator('dashboardLink'))->click();
    }

    public function hasUnreadNotification(): bool
    {
        return $this->getHTMLPage()
            ->setTimeout(5)
            ->findAll($this->getLocator('pendingNotification'))
            ->any();
    }

    public function search(string $searchInput): void
    {
        $this->getHTMLPage()->find($this->getLocator('searchInput'))->setValue($searchInput);
        $this->getHTMLPage()->find($this->getLocator('searchButton'))->click();
    }

    public function openNotifications(): void
    {
        $this->getHTMLPage()->find($this->getLocator('userNotifications'))->click();
    }

    public function chooseFromUserDropdown(string $option): void
    {
        $this->getHTMLPage()->find($this->getLocator('userSettingsToggle'))->click();
        $this->getHTMLPage()->findAll($this->getLocator('userSettingsItem'))->getByCriterion(new ElementTextCriterion($option))->click();
    }

    public function setFocusMode(bool $expectedModeStatus): void
    {
        $this->getHTMLPage()->find($this->getLocator('userSettingsToggle'))->click();

        $isEnabled = $this->getHTMLPage()->setTimeout(3)->findAll($this->getLocator('userFocusEnabled'))->any();

        if ($expectedModeStatus != $isEnabled) {
            $this->getHTMLPage()->find($this->getLocator('userFocusMode'))->click();
        } else {
            $this->getHTMLPage()->find($this->getLocator('userSettingsToggle'))->click();
        }

        if ($expectedModeStatus) {
            $this->getHTMLPage()->find($this->getLocator('focusModeBadge'))->assert()->textEquals('Focus mode');
        } else {
            $this->getHTMLPage()->findAll($this->getLocator('focusModeBadge'))->assert()->isEmpty();
        }
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->find($this->getLocator('userSettingsToggle'))->assert()->isVisible();
    }

    public function selectSiteContext(string $siteName): void
    {
        $this->getHTMLPage()->find($this->getLocator('siteDropdown'))->click();
        $this->ibexaDropdown->selectOptionByValueFragment($siteName);
        $this->getHTMLPage()->setTimeout(5)->find($this->getLocator('siteDropdownSelectedItem'))->assert()->textEquals(sprintf('Site: %s', $siteName));
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('dashboardLink', '.ibexa-main-header__brand'),
            new VisibleCSSLocator('pendingNotification', '.ibexa-header-user-menu__notice-dot'),
            new VisibleCSSLocator('userSettingsToggle', '.ibexa-header-user-menu__toggler'),
            new VisibleCSSLocator('userNotifications', '.ibexa-header-user-menu__notifications-toggler'),
            new VisibleCSSLocator('userSettingsItem', '.ibexa-popup-menu__item'),
            new VisibleCSSLocator('userSettingsPopup', '.ibexa-header-user-menu .ibexa-header-user-menu__popup-menu'),
            new VisibleCSSLocator('searchInput', '.ibexa-main-header #search_query'),
            new VisibleCSSLocator('searchButton', '.ibexa-main-header .ibexa-input-text-wrapper__action-btn--search'),
            new VisibleCSSLocator('userFocusEnabled', '[name="focus_mode_change"] .ibexa-toggle__label--on'),
            new VisibleCSSLocator('userFocusMode', '[name="focus_mode_change"] .ibexa-toggle__switcher'),
            new VisibleCSSLocator('focusModeBadge', '.ibexa-user-mode-badge'),
            new VisibleCSSLocator('siteDropdown', '.ibexa-preview-context-switch-form .ibexa-dropdown'),
            new VisibleCSSLocator('siteDropdownSelectedItem', '.ibexa-preview-context-switch-form .ibexa-dropdown li.ibexa-dropdown__selected-item:nth-of-type(1)'),
        ];
    }
}
