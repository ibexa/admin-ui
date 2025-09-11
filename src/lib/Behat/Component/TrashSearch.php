<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Behat\Mink\Session;
use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\Condition\ElementExistsCondition;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

class TrashSearch extends Component
{
    private IbexaDropdown $ibexaDropdown;

    public function __construct(Session $session, IbexaDropdown $ibexaDropdown)
    {
        parent::__construct($session);
        $this->ibexaDropdown = $ibexaDropdown;
    }

    public function submitSearchText(string $searchQuery): void
    {
        $this->getHTMLPage()->setTimeout(3)->find($this->getLocator('mainSearchBoxInput'))->setValue($searchQuery);
    }

    public function confirmSearch(): void
    {
        $this->getHTMLPage()->setTimeout(3)->find($this->getLocator('mainSearchBoxConfirmButton'))->click();
    }

    public function filterByContentType(string $contentType): void
    {
        $this->getHTMLPage()->setTimeout(3)->find($this->getLocator('contentTypeFilterDropdown'))->click();

        $this->ibexaDropdown->verifyIsLoaded();
        $this->ibexaDropdown->selectOption($contentType);
    }

    public function filterBySection(string $section): void
    {
        $this->getHTMLPage()->setTimeout(3)->find($this->getLocator('sectionFilterDropdown'))->click();

        $this->ibexaDropdown->verifyIsLoaded();
        $this->ibexaDropdown->selectOption($section);
    }

    public function filterByContentItemCreator(string $contentItemCreator): void
    {
        $this->getHTMLPage()->setTimeout(3)->find($this->getLocator('creatorSearchBoxInput'))->setValue($contentItemCreator);

        $creatorsDropdownItemLocator = $this->getLocator('creatorFilterDropdown');
        $this->getHTMLPage()
            ->setTimeout(5)
            ->waitUntilCondition(new ElementExistsCondition($this->getHTMLPage(), $creatorsDropdownItemLocator))
            ->findAll($creatorsDropdownItemLocator)
            ->getByCriterion(new ElementTextCriterion($contentItemCreator))
            ->click();
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->setTimeout(5)->find($this->getLocator('mainSearchBox'))->isVisible();
    }

    protected function specifyLocators(): array
    {
        return
        [
        new VisibleCSSLocator('mainSearchBox', '.ibexa-adaptive-filters--inside-container'),
        new VisibleCSSLocator('mainSearchBoxInput', '#trash_search_content_name'),
        new VisibleCSSLocator('creatorSearchBoxInput', '.ibexa-trash-search-form__item--creator .ibexa-input--text'),
        new VisibleCSSLocator('mainSearchBoxConfirmButton', '.ibexa-adaptive-filters__static-left .ibexa-input-text-wrapper--type-text .ibexa-input-text-wrapper__action-btn--search'),
        new VisibleCSSLocator('contentTypeFilterDropdown', 'label[for="trash_search_content_type"] ~ .ibexa-dropdown'),
        new VisibleCSSLocator('sectionFilterDropdown', 'label[for="trash_search_section"] ~ .ibexa-dropdown'),
        new VisibleCSSLocator('creatorFilterDropdown', '.ibexa-trash-search-form__user-list li'),
            ];
    }
}
