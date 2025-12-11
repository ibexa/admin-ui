<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Ibexa\AdminUi\Behat\Page\ChangePasswordPage;
use Ibexa\AdminUi\Behat\Page\UserSettingsPage;

final readonly class UserPreferencesContext implements Context
{
    public function __construct(
        private ChangePasswordPage $changePasswordPage,
        private UserSettingsPage $userSettingsPage
    ) {
    }

    /**
     * @Given I switch to :tabName tab in User settings
     */
    public function iSwitchToTabInUserSettings(string $tabName): void
    {
        $this->userSettingsPage->switchTab($tabName);
    }

    /**
     * @Given I click on the change password button
     */
    public function iClickChangePasswordButton(): void
    {
        $this->userSettingsPage->changePassword();
    }

    /**
     * @When I change password from :oldPassword to :newPassword
     */
    public function iChangePassword(string $oldPassword, string $newPassword): void
    {
        $this->changePasswordPage->verifyIsLoaded();
        $this->changePasswordPage->setOldPassword($oldPassword);
        $this->changePasswordPage->setNewPassword($newPassword);
        $this->changePasswordPage->setConfirmPassword($newPassword);
    }

    /**
     * @When I disable autosave
     */
    public function iSetAutosaveDraftValue(): void
    {
        $this->userSettingsPage->openAutosaveDraftEditionPage();
        $this->userSettingsPage->verifyIsLoaded();
        $this->userSettingsPage->disableAutosave();
    }

    /**
     * @Given I disable Help center
     */
    public function iDisableHelpCenter(): void
    {
        $this->userSettingsPage->openBrowsingEditionPage();
        $this->userSettingsPage->verifyIsLoaded();
        $this->userSettingsPage->disableHelpCenter();
    }
}
