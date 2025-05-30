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

class ContentTypePicker extends Component
{
    private IbexaDropdown $ibexaDropdown;

    public const MINIMUM_ITEMS_COUNT_FOR_SEARCH_INPUT = 10;

    public function __construct(Session $session, IbexaDropdown $ibexaDropdown)
    {
        parent::__construct($session);
        $this->ibexaDropdown = $ibexaDropdown;
    }

    public function select(string $contentTypeName): void
    {
        $countBeforeFiltering = $this->getDisplayedItemsCount();
        if ($countBeforeFiltering > self::MINIMUM_ITEMS_COUNT_FOR_SEARCH_INPUT) {
            $this->getHTMLPage()->find($this->getLocator('filterInput'))->clear();
            $this->getHTMLPage()->find($this->getLocator('filterInput'))->setValue($contentTypeName);
            $this->getHTMLPage()->setTimeout(3)->waitUntil(function () use ($countBeforeFiltering): bool {
                return $this->getDisplayedItemsCount() < $countBeforeFiltering;
            }, 'The number of displayed content types did not decrease after filtering.');
        }

        $this->clickOnItem($contentTypeName);
    }

    public function clickOnItem(string $contentTypeName): void
    {
        $this->getHTMLPage()
            ->findAll($this->getLocator('contentTypeItem'))
            ->getByCriterion(new ElementTextCriterion($contentTypeName))
            ->click();
    }

    public function selectLanguage(string $language): void
    {
        $this->getHTMLPage()->find($this->getLocator('languageDropdown'))->click();
        $this->ibexaDropdown->selectOption($language);
    }

    protected function getDisplayedItemsCount(): int
    {
        return $this->getHTMLPage()->findAll($this->getLocator('contentTypeItem'))->count();
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->setTimeout(3)->find($this->getLocator('header'))->assert()->textEquals('Create content');
    }

    public function confirm(): void
    {
        $this->getHTMLPage()->find($this->getLocator('createButton'))->click();
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('filterInput', '.ibexa-content-menu-wrapper .ibexa-extra-actions__section-content--content-type .ibexa-instant-filter__input, .c-udw-tab .ibexa-extra-actions__section-content--content-type .ibexa-instant-filter__input'),
            new VisibleCSSLocator('contentTypeItem', '.ibexa-content-menu-wrapper .ibexa-extra-actions__section-content--content-type .ibexa-instant-filter__group-item:not([hidden]) .form-check-label, .c-udw-tab .ibexa-extra-actions__section-content--content-type .ibexa-instant-filter__group-item:not([hidden]) .form-check-label'),
            new VisibleCSSLocator('header', '.ibexa-content-menu-wrapper .ibexa-extra-actions--create .ibexa-extra-actions__header h2'),
            new VisibleCSSLocator('languageDropdown', '.ibexa-content-menu-wrapper .ibexa-dropdown__selection-info'),
            new VisibleCSSLocator('createButton', '.c-content-create__confirm-button, [id="content_create_create"]'),
        ];
    }
}
