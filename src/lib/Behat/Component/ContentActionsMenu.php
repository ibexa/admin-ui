<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

class ContentActionsMenu extends Component
{
    public function clickButton(string $buttonName): void
    {
        $buttons = $this->getHTMLPage()
            ->findAll($this->getLocator('menuButton'))
            ->filterBy(new ElementTextCriterion($buttonName));

        // TODO: Remove parts of this logic once redeisgn is fully done
        if ($buttons->any()) {
            $button = $buttons->first();
            $button->mouseOver();

            if ($button->findAll($this->getLocator('label'))->any()) {
                $button->find($this->getLocator('label'))->click();

                return;
            }

            $button->click();

            return;
        }

        $contextMenuSplitBtnsTogglers = $this->getHTMLPage()
            ->findAll($this->getLocator('menuSplitToggler'));

        foreach ($contextMenuSplitBtnsTogglers->getIterator() as $splitBtnToggler) {
            $splitBtnToggler->click();

            $matchingSubButtons = $this->getHTMLPage()
                ->findAll($this->getLocator('menuSplitSubmenuButton'))
                ->filterBy(new ElementTextCriterion($buttonName));

            if ($matchingSubButtons->any()) {
                $matchingSubButtons->first()->click();
            }
        }

        $this->getHTMLPage()->find($this->getLocator('moreButton'))->click();

        $this->getHTMLPage()
            ->findAll($this->getLocator('expandedMenuButton'))
            ->getByCriterion(new ElementTextCriterion($buttonName))
            ->click();
    }

    public function isButtonActive(string $buttonName): bool
    {
        $moreButton = $this->getHTMLPage()->findAll($this->getLocator('moreButton'));
        if ($moreButton->any()) {
            $moreButton->single()->click();
        }

        return !$this->getHTMLPage()->findAll($this->getLocator('menuButton'))->getByCriterion(new ElementTextCriterion($buttonName))->hasAttribute('disabled');
    }

    public function isButtonVisible(string $buttonName): bool
    {
        $moreButton = $this->getHTMLPage()->findAll($this->getLocator('moreButton'));
        if ($moreButton->any()) {
            $moreButton->single()->click();
        }

        return $this->getHTMLPage()
            ->findAll($this->getLocator('menuButton'))
            ->filterBy(new ElementTextCriterion($buttonName))
            ->any();
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()
            ->setTimeout(5)
            ->find($this->getLocator('menuButton'))
            ->assert()->isVisible();
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('menuButton', '.ibexa-context-menu .ibexa-btn, .ibexa-context-menu__item .ibexa-popup-menu__item, .ibexa-context-menu .btn'), // TO DO: set one selector after redesign
            new VisibleCSSLocator('menuSplitToggler', '.ibexa-context-menu .ibexa-split-btn__toggle-btn'),
            new VisibleCSSLocator('menuSplitSubmenuButton', '.ibexa-popup-menu:not(.ibexa-popup-menu--hidden) .ibexa-popup-menu__item-content'),
            new VisibleCSSLocator('label', '.ibexa-btn__label'),
            new VisibleCSSLocator('moreButton', '.ibexa-context-menu__item--more'),
            new VisibleCSSLocator('expandedMenuButton', '.ibexa-context-menu__item .ibexa-popup-menu__item-content'),
        ];
    }
}
