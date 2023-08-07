<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Ibexa\AdminUi\Behat\Page\ChangePasswordPage;
use Ibexa\AdminUi\Behat\Page\UserSettingsPage;

class UserPreferencesContext implements Context
{
    private ChangePasswordPage $changePasswordPage;

    private UserSettingsPage $userSettingsPage;

    public function __construct(ChangePasswordPage $changePasswordPage, UserSettingsPage $userSettingsPage)
    {
        $this->changePasswordPage = $changePasswordPage;
        $this->userSettingsPage = $userSettingsPage;
    }

    /**
     * @Given I switch to :tabName tab in User settings
     */
    public function iSwitchToTabInUserSettings($tabName): void
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
    public function iChangePassword($oldPassword, $newPassword): void
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
}
