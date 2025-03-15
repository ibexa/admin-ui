<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Ibexa\AdminUi\Behat\Component\Breadcrumb;
use Ibexa\AdminUi\Behat\Component\LeftMenu;
use Ibexa\AdminUi\Behat\Component\UpperMenu;
use Ibexa\AdminUi\Behat\Page\ContentUpdateItemPage;
use Ibexa\AdminUi\Behat\Page\ContentViewPage;
use Ibexa\Behat\Browser\Page\PageRegistry;
use Ibexa\Behat\Core\Behat\ArgumentParser;

class NavigationContext implements Context
{
    private ArgumentParser $argumentParser;

    /** @var \Ibexa\Behat\Browser\Page\PageRegistry[] */
    private PageRegistry $pageRegistry;

    private UpperMenu $upperMenu;

    private LeftMenu $leftMenu;

    private Breadcrumb $breadcrumb;

    private ContentViewPage $contentViewPage;

    private ContentUpdateItemPage $contentUpdateItemPage;

    public function __construct(
        ArgumentParser $argumentParser,
        UpperMenu $upperMenu,
        LeftMenu $leftMenu,
        Breadcrumb $breadcrumb,
        ContentViewPage $contentViewPage,
        PageRegistry $pageRegistry,
        ContentUpdateItemPage $contentUpdateItemPage
    ) {
        $this->argumentParser = $argumentParser;
        $this->pageRegistry = $pageRegistry;
        $this->upperMenu = $upperMenu;
        $this->leftMenu = $leftMenu;
        $this->breadcrumb = $breadcrumb;
        $this->contentViewPage = $contentViewPage;
        $this->contentUpdateItemPage = $contentUpdateItemPage;
    }

    /**
     * @Given I open :pageName page in admin SiteAccess
     * @Given I open the :pageName page in admin SiteAccess
     */
    public function openPage(string $pageName): void
    {
        $page = $this->pageRegistry->get($pageName);
        $page->open('admin');
        $page->verifyIsLoaded();
    }

    /**
     * @Given I try to open :pageName page in admin SiteAccess
     */
    public function tryToOpenPage(string $pageName): void
    {
        $this->pageRegistry->get($pageName)->tryToOpen('admin');
    }

    /**
     * @Given I go to user settings
     */
    public function iGoToUserSettings(): void
    {
        $this->upperMenu->chooseFromUserDropdown('User settings');
    }

    /**
     * @Given I go to user profile
     */
    public function iGoToUserProfile(): void
    {
        $this->upperMenu->chooseFromUserDropdown('Profile');
    }

    /**
     * @Then /^I should be on "?([^\"]*)"? page$/
     */
    public function iAmOnPage(string $pageName): void
    {
        $this->pageRegistry->get($pageName)->verifyIsLoaded();
    }

    /**
     * @Then I go to :tab tab
     */
    public function iGoToTab(string $tabName): void
    {
        $this->leftMenu->goToTab($tabName);
    }

    /**
     * @Then I go to :subTab in :tab tab
     */
    public function iGoToSubTab(string $tab, string $subTab): void
    {
        $this->leftMenu->goToSubTab($tab, $subTab);
    }

    /**
     * @Then I change subtab to :subTab
     */
    public function iChangeSubTab(string $subTab): void
    {
        $this->leftMenu->changeSubTab($subTab);
    }

    /**
     * @When I click on :element on breadcrumb
     */
    public function iClickOnBreadcrumbLink(string $element): void
    {
        $this->breadcrumb->verifyIsLoaded();
        $this->breadcrumb->clickBreadcrumbItem($element);
    }

    /**
     * @Given I navigate to content :contentName of type :contentType in :path
     */
    public function iNavigateToContent(string $contentName, string $contentType, string $path = null): void
    {
        $expectedContentPath = sprintf('%s/%s', $path, $contentName);
        $pathParts = explode('/', $expectedContentPath);
        if ('root' === $pathParts[0]) {
            $startingLocation = '/';
        } else {
            $startingLocation = $pathParts[0];
        }
        $expectedContentPath = $this->argumentParser->replaceRootKeyword($expectedContentPath);
        $this->contentViewPage->setExpectedLocationPath($startingLocation);
        $this->contentViewPage->navigateToPath($expectedContentPath);
    }

    /**
     * @Given I go to user notifications
     */
    public function iGoToUserNotifications(): void
    {
        $this->upperMenu->openNotifications();
    }

    /**
     * @Given I log out of back office
     */
    public function iLogOutOfBackOffice(): void
    {
        $this->upperMenu->chooseFromUserDropdown('Logout');
    }

    /**
     * @Given I'm on Content view Page for :path
     * @Given there exists Content view Page for :path
     */
    public function iMOnContentViewPageFor(string $path): void
    {
        $path = $this->argumentParser->parseUrl($path);
        $this->contentViewPage->setExpectedLocationPath($path);
        $this->contentViewPage->open('admin');
        $this->contentViewPage->verifyIsLoaded();
    }

    /**
     * @Given I should be on Content view Page for :path
     */
    public function iShouldBeOnContentViewPage(string $path): void
    {
        $path = $this->argumentParser->parseUrl($path);
        $this->contentViewPage->setExpectedLocationPath($path);
        $this->contentViewPage->verifyIsLoaded();
    }

    /**
     * @Given I should be on Content update page for :contentItemName
     */
    public function iShouldBeOnContentUpdatePageForItem(string $contentItemName = ''): void
    {
        $this->contentUpdateItemPage->setExpectedPageTitle($contentItemName);
        $this->contentUpdateItemPage->verifyIsLoaded();
    }
}
