<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Behat\Mink\Session;
use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\Condition\ElementTransitionHasEndedCondition;
use Ibexa\Behat\Browser\Element\Criterion\ChildElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

class CreateNewPopup extends Component
{
    private IbexaDropdown $ibexaDropdown;

    public function __construct(Session $session, IbexaDropdown $ibexaDropdown)
    {
        parent::__construct($session);
        $this->ibexaDropdown = $ibexaDropdown;
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()
            ->setTimeout(5)
            ->waitUntilCondition(new ElementTransitionHasEndedCondition($this->getHTMLPage(), $this->getLocator('popup')));
        $this->getHTMLPage()->setTimeout(5)->find($this->getLocator('popup'))->assert()->isVisible();
    }

    public function verifyHeaderText(string $expectedHeader): void
    {
        $this->getHTMLPage()->setTimeout(5)->find($this->getLocator('popupHeader'))->assert()->textEquals($expectedHeader);
    }

    public function selectFromDropdown(string $dropdownLabel, string $dropdownValue): void
    {
        $definition = $this->getHTMLPage()->setTimeout(5)->find($this->getLocator('popup'))->findAll($this->getLocator('formGroup'))
            ->getByCriterion(new ChildElementTextCriterion($this->getLocator('dropdownLabel'), $dropdownLabel));
        if ($definition->find($this->getLocator('dropdownValue'))->getText() === $dropdownValue) {
            return;
        }

        $definition->find($this->getLocator('dropdown'))->click();
        $this->ibexaDropdown->verifyIsLoaded();
        $this->ibexaDropdown->selectOption($dropdownValue);
    }

    public function confirm(): void
    {
        $this->getHTMLPage()->find($this->getLocator('addButton'))->click();
    }

    public function decline(): void
    {
        $this->getHTMLPage()->find($this->getLocator('cancelButton'))->click();
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('popupHeader', '.ibexa-extra-actions__header'),
            new VisibleCSSLocator('addButton', '.ibexa-extra-actions__pre-form-btns .ibexa-btn--primary, .ibexa-extra-actions--create .ibexa-btn--primary'),
            new VisibleCSSLocator('cancelButton', '.ibexa-extra-actions__pre-form-btns .ibexa-btn--secondary'),
            new VisibleCSSLocator('popup', '.ibexa-extra-actions:not(.ibexa-extra-actions--hidden)'),
            new VisibleCSSLocator('formGroup', '.form-group'),
            new VisibleCSSLocator('dropdown', '.ibexa-dropdown'),
            new VisibleCSSLocator('dropdownLabel', '.ibexa-label'),
            new VisibleCSSLocator('dropdownValue', '.ibexa-dropdown__selection-info'),
        ];
    }
}
