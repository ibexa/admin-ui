<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Ibexa\AdminUi\Behat\Page\UserProfilePage;

class UserProfileContext implements Context
{
    private UserProfilePage $userProfilePage;

    public function __construct(UserProfilePage $userProfilePage)
    {
        $this->userProfilePage = $userProfilePage;
    }

    /**
     * @Given I edit user profile summary
     */
    public function editUserProfileSummary(): void
    {
        $this->userProfilePage->editSummary();
    }

    /**
     * @Then I should see a user profile summary with values
     */
    public function iVerifyUserProfileSummary(TableNode $table): void
    {
        $this->userProfilePage->verifyIsLoaded();
        foreach ($table->getHash() as $row) {
            $this->userProfilePage->verifyUserProfileSummary($row['Full name'], $row['Email'], $row['Job Title'], $row['Department'], $row['Location']);
        }
    }
}
