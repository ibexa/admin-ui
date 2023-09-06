<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Ibexa\AdminUi\Behat\Component\ContentActionsMenu;
use PHPUnit\Framework\Assert;

class ContentActionsMenuContext implements Context
{
    /** @var \Ibexa\AdminUi\Behat\Component\ContentActionsMenu */
    private $contentActionsMenu;

    public function __construct(ContentActionsMenu $contentActionsMenu)
    {
        $this->contentActionsMenu = $contentActionsMenu;
    }

    /**
     * @Given I click (on) the edit action bar button :buttonName
     * @Given I perform the :buttonName action
     * @Given I perform the :buttonName action from the :groupName group
     */
    public function clickEditActionBar(string $buttonName, string $groupName = null): void
    {
        $this->contentActionsMenu->clickButton($buttonName, $groupName);
    }

    /**
     * @Given the buttons are disabled
     */
    public function theButtonsAreDisabled(TableNode $buttons): void
    {
        foreach ($buttons->getHash() as $button) {
            Assert::assertFalse($this->contentActionsMenu->isButtonActive($button['buttonName']));
        }
    }

    /**
     * @Given the :buttonName button is not visible
     */
    public function buttonIsNotVisible(string $buttonName): void
    {
        Assert::assertFalse($this->contentActionsMenu->isButtonVisible($buttonName));
    }

    /**
     * @Given the :buttonName button is visible
     */
    public function buttonIsVisible(string $buttonName): void
    {
        Assert::assertTrue($this->contentActionsMenu->isButtonVisible($buttonName));
    }
}
