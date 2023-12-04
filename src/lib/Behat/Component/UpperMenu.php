<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\Condition\ElementNotExistsCondition;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use PHPUnit\Framework\Assert;

class UpperMenu extends Component
{
    private const USER_MODES = ['Smart', 'Expert'];

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

    public function switchToUserMode(string $newMode): void
    {
        $this->getHTMLPage()->find($this->getLocator('userSettingsToggle'))->click();

        $currentMode = explode(
            ' ',
            $this->getHTMLPage()->find($this->getLocator('userMode'))->getText()
        )[0];

        if (strtolower($newMode) !== strtolower($currentMode)) {
            $this->getHTMLPage()->find($this->getLocator('userMode'))->click();
            $this->getHTMLPage()
                ->waitUntilCondition(
                    new ElementNotExistsCondition($this->getHTMLPage(), $this->getLocator('userSettingsPopup'))
                );
        } else {
            $this->getHTMLPage()->find($this->getLocator('userSettingsToggle'))->click();
        }
    }

    public function getCurrentUserMode(): string
    {
        $this->getHTMLPage()->find($this->getLocator('userSettingsToggle'))->click();

        $mode = explode(
            ' ',
            $this->getHTMLPage()->find($this->getLocator('userMode'))->getText()
        )[0];
        Assert::assertContains($mode, self::USER_MODES);

        $this->getHTMLPage()->find($this->getLocator('userSettingsToggle'))->click();

        return $mode;
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->find($this->getLocator('userSettingsToggle'))->assert()->isVisible();
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('dashboardLink', '.ibexa-main-header__brand'),
            new VisibleCSSLocator('pendingNotification', '.ibexa-header-user-menu__notice-dot'),
            new VisibleCSSLocator('userSettingsToggle', '.ibexa-header-user-menu'),
            new VisibleCSSLocator('userNotifications', '.ibexa-header-user-menu__notifications-toggler'),
            new VisibleCSSLocator('userSettingsItem', '.ibexa-popup-menu__item'),
            new VisibleCSSLocator('userSettingsPopup', '.ibexa-header-user-menu .ibexa-header-user-menu__popup-menu'),
            new VisibleCSSLocator('searchInput', '.ibexa-main-header #search_query'),
            new VisibleCSSLocator('searchButton', '.ibexa-main-header .ibexa-input-text-wrapper__action-btn--search'),
            new VisibleCSSLocator('userMode', '[name="user_mode_change"] .ibexa-toggle__label'),
        ];
    }
}
