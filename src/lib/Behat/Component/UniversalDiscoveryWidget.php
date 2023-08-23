<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\Condition\ElementExistsCondition;
use Ibexa\Behat\Browser\Element\Condition\ElementHasTextCondition;
use Ibexa\Behat\Browser\Element\Condition\ElementNotExistsCondition;
use Ibexa\Behat\Browser\Element\Criterion\ElementAttributeCriterion;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Element\ElementInterface;
use Ibexa\Behat\Browser\Locator\CSSLocator;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

class UniversalDiscoveryWidget extends Component
{
    private const LONG_TIMEOUT = 20;
    private const SHORT_TIMEOUT = 2;

    public function selectContent(string $itemPath): void
    {
        $pathParts = explode('/', $itemPath);
        $level = 1;

        foreach ($pathParts as $itemName) {
            $this->selectTreeBranch($itemName, $level);
            ++$level;
        }

        $itemName = $pathParts[count($pathParts) - 1];

        if ($this->isMultiSelect()) {
            $this->addItemToMultiSelection($itemName, count($pathParts));

            return;
        }

        $this->getHTMLPage()
            ->setTimeout(5)
            ->waitUntilCondition(
                new ElementHasTextCondition($this->getHTMLPage(), $this->getLocator('selectedItemName'), $itemName)
            )->find($this->getLocator('selectedItemName'))
            ->assert()->isVisible();
        $this->getHTMLPage()->setTimeout(3)->find($this->getLocator('previewImage'))->assert()->isVisible();
    }

    public function confirm(): void
    {
        $this->getHTMLPage()->find($this->getLocator('confirmButton'))->click();
        $this->getHTMLPage()
            ->setTimeout(5)
            ->waitUntilCondition(new ElementNotExistsCondition($this->getHTMLPage(), $this->getLocator('udw')));
    }

    public function cancel(): void
    {
        $this->getHTMLPage()->find($this->getLocator('cancelButton'))->click();
    }

    public function openSearch(): void
    {
        $this->getHTMLPage()->find($this->getLocator('searchButton'))->click();
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->find($this->getLocator('udw'))->assert()->isVisible();
    }

    protected function isMultiSelect(): bool
    {
        return $this->getHTMLPage()->setTimeout(0)->findAll($this->getLocator('multiselect'))->any();
    }

    protected function addItemToMultiSelection(string $itemName, int $level): void
    {
        $treeElementsLocator = new CSSLocator('', sprintf($this->getLocator('treeLevelElementsFormat')->getSelector(), $level));
        $this->getHTMLPage()->findAll($treeElementsLocator)->getByCriterion(new ElementTextCriterion($itemName))->find($this->getLocator('input'))->click();
    }

    protected function selectTreeBranch(string $itemName, int $level): void
    {
        $treeLevelLocator = new VisibleCSSLocator('treeLevelLocator', sprintf($this->getLocator('treeLevelElementsFormat')->getSelector(), $level));
        $this->getHTMLPage()->setTimeout(self::LONG_TIMEOUT)->find($treeLevelLocator)->assert()->isVisible();

        $alreadySelectedItemName = $this->getCurrentlySelectedItemName($level);

        if ($itemName === $alreadySelectedItemName) {
            // don't do anything, this level is already selected

            return;
        }

        // when the tree is loaded further for the already selected item we need to make sure it's reloaded properly
        $willNextLevelBeReloaded = null !== $alreadySelectedItemName && $this->isNextLevelDisplayed($level);

        if ($willNextLevelBeReloaded) {
            $currentItems = $this->getItemsFromLevel($level + 1);
        }

        $treeElementsLocator = new CSSLocator('', sprintf($this->getLocator('treeLevelElementsFormat')->getSelector(), $level));
        $selectedTreeElementLocator = new CSSLocator('', sprintf($this->getLocator('treeLevelSelectedFormat')->getSelector(), $level));

        $this->getHTMLPage()->findAll($treeElementsLocator)->getByCriterion(new ElementTextCriterion($itemName))->find($this->getLocator('elementName'))->click();
        $this->getHTMLPage()->findAll($selectedTreeElementLocator)->getByCriterion(new ElementTextCriterion($itemName))->assert()->isVisible();

        if ($willNextLevelBeReloaded) {
            // Wait until the items displayed previously disappear or change
            $this->getHTMLPage()->setTimeout(self::LONG_TIMEOUT)->waitUntil(function () use ($currentItems, $level) {
                return !$this->isNextLevelDisplayed($level) || $this->getItemsFromLevel($level + 1) !== $currentItems;
            }, 'Items in UDW did not refresh correctly');
        }
    }

    protected function getItemsFromLevel(int $level): array
    {
        $levelItemsSelector = new CSSLocator('css', sprintf($this->getLocator('treeLevelElementsFormat')->getSelector(), $level));

        return $this->getHTMLPage()->setTimeout(self::LONG_TIMEOUT)->findAll($levelItemsSelector)->map(
            static function (ElementInterface $element) {
                return $element->getText();
            }
        );
    }

    public function bookmarkContentItem(): void
    {
        $this->getHTMLPage()->setTimeout(3)->find($this->getLocator('bookmarkButton'))->click();
        $this->getHTMLPage()->setTimeout(3)->waitUntil(function () {
            return $this->isBookmarked();
        }, 'The icon did not change to bookmarked one');
    }

    public function isBookmarked(): bool
    {
        return $this->getHTMLPage()->find($this->getLocator('bookmarkButton'))->getText() === 'Remove from bookmarks';
    }

    public function changeTab($tabName): void
    {
        $tab = $this->getHTMLPage()
            ->findAll($this->getLocator('categoryTabSelector'))
            ->getByCriterion(new ElementAttributeCriterion('data-original-title', $tabName));
        $tab->click();
        $tab->setTimeout(self::SHORT_TIMEOUT)->waitUntilCondition(new ElementExistsCondition($tab, $this->getLocator('selectedTab')));
    }

    public function selectBookmark(string $bookmarkName): void
    {
        $this->getHTMLPage()
             ->setTimeout(5)
             ->findAll($this->getLocator('bookmarkedItem'))
             ->getByCriterion(new ElementTextCriterion($bookmarkName))
             ->click();

        $this->getHTMLPage()->find($this->getLocator('markedBookmarkedItem'))->assert()->textEquals($bookmarkName);
        $this->getHTMLPage()->setTimeout(5)->find($this->getLocator('contentPreview'))->assert()->isVisible();
    }

    public function editSelectedContent(): void
    {
        $this->getHTMLPage()->setTimeout(self::SHORT_TIMEOUT)->find($this->getLocator('editButton'))->click();
        $iframeLocator = $this->getLocator('iframe');
        $script = sprintf("document.querySelector('%s').setAttribute('name','editIframe')", $iframeLocator->getSelector());
        $this->getHTMLPage()->setTimeout(self::SHORT_TIMEOUT)->waitUntilCondition(
            new ElementExistsCondition($this->getHTMLPage(), $iframeLocator)
        );
        $this->getHTMLPage()->executeJavaScript($script);
        $this->getSession()->switchToIFrame('editIframe');
    }

    public function searchForContent(string $name): void
    {
        $this->getHTMLPage()->find($this->getLocator('inputField'))->setValue($name);
        $this->getHTMLPage()->find($this->getLocator('searchButton'))->click();

        $this->getHTMLPage()
            ->setTimeout(self::SHORT_TIMEOUT)
            ->find($this->getLocator('searchResults'))
            ->assert()->textContains('Results for');
    }

    public function selectInSearchResults(string $name): void
    {
        $this->getHTMLPage()
            ->find($this->getLocator('targetResult'))
            ->assert()->textEquals($name)
            ->click();
    }

    protected function specifyLocators(): array
    {
        return [
            // general selectors
            new VisibleCSSLocator('udw', '.m-ud'),
            new CSSLocator('confirmButton', '.c-actions-menu__confirm-btn'),
            new CSSLocator('cancelButton', '.c-top-menu__cancel-btn'),
            new CSSLocator('mainWindow', '.m-ud'),
            new CSSLocator('selectedLocationsTab', '.c-selected-locations'),
            new CSSLocator('categoryTabSelector', '.c-tab-selector__item'),
            new CSSLocator('selectedTab', '.c-tab-selector__item--selected'),
            new VisibleCSSLocator('iframe', '.c-content-edit__iframe'),
            new VisibleCSSLocator('multiselect', '.m-ud .c-finder-leaf .ibexa-input--checkbox'),
            new VisibleCSSLocator('selectedItemName', '.c-content-meta-preview__content-name'),
            new VisibleCSSLocator('previewImage', '.c-content-meta-preview__preview'),
            // selectors for path traversal
            new CSSLocator('treeLevelFormat', '.c-finder-branch:nth-child(%d)'),
            new CSSLocator('treeLevelElementsFormat', 'div.c-finder-branch:nth-of-type(%d) .c-finder-leaf'),
            new CSSLocator('elementName', '.c-finder-leaf__name'),
            new CSSLocator('input', '.c-udw-toggle-selection'),
            new CSSLocator('treeLevelSelectedFormat', 'div.c-finder-branch:nth-of-type(%d) .c-finder-leaf--marked'),
            // itemActions
            new VisibleCSSLocator('contentPreview', '.c-content-meta-preview'),
            new CSSLocator('editButton', '.c-content-edit-button__btn'),
            // bookmarks
            new VisibleCSSLocator('bookmarkButton', '.c-content-meta-preview__toggle-bookmark-button'),
            new VisibleCSSLocator('bookmarkPanel', '.c-bookmarks-list'),
            new VisibleCSSLocator('bookmarkedItem', '.c-bookmarks-list__item-name'),
            new VisibleCSSLocator('markedBookmarkedItem', '.c-bookmarks-list__item--marked'),
            // search
            new VisibleCSSLocator('inputField', '.c-top-menu-search-input__search-input'),
            new VisibleCSSLocator('searchButton', '.c-top-menu-search-input__search-btn'),
            new VisibleCSSLocator('searchResults', '.c-search__table-title'),
            new VisibleCSSLocator('targetResult', '.ibexa-table__row td:nth-child(2)'),
        ];
    }

    private function getCurrentlySelectedItemName(int $level): ?string
    {
        $selectedElementSelector = new CSSLocator(
            'selectedElement',
            sprintf($this->getLocator('treeLevelSelectedFormat')->getSelector(), $level)
        );

        $elements = $this->getHTMLPage()->setTimeout(self::SHORT_TIMEOUT)->findAll($selectedElementSelector);

        return $elements->any() ? $elements->first()->getText() : null;
    }

    private function isNextLevelDisplayed(int $currentLevel): bool
    {
        return $this->getHTMLPage()->
            setTimeout(self::SHORT_TIMEOUT)->
            findAll(
                new CSSLocator(
                    'css',
                    sprintf($this->getLocator('treeLevelElementsFormat')->getSelector(), $currentLevel + 1)
                )
            )->any();
    }
}
