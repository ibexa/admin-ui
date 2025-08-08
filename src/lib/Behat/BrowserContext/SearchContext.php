<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Ibexa\AdminUi\Behat\Component\UpperMenu;
use Ibexa\AdminUi\Behat\Page\SearchPage;
use PHPUnit\Framework\Assert;

final readonly class SearchContext implements Context
{
    public function __construct(
        private SearchPage $searchPage,
        private UpperMenu $upperMenu
    ) {
    }

    /**
     * @When I search for a Content named :contentItemName
     */
    public function iSearchForContent(string $contentItemName): void
    {
        $this->upperMenu->search($contentItemName);
        $this->searchPage->verifyIsLoaded();
    }

    /**
     * @Then I should see in search results an item named :contentItemName
     */
    public function searchResults(string $contentItemName): void
    {
        Assert::assertTrue($this->searchPage->isElementInResults(['Name' => $contentItemName]));
    }
}
