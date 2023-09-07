<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Exception\TimeoutException;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

class ContentActionsMenu extends Component
{
    use \Ibexa\Behat\Core\Debug\InteractiveDebuggerTrait;

    public function clickButton(string $buttonName, ?string $groupName = null): void
    {
//        $this->setInteractiveBreakpoint(get_defined_vars());

        if ($groupName === null) {
            $this->clickStandaloneButton($buttonName);

            return;
        }

        $this->clickButtonInGroup($groupName, $buttonName);
    }

    private function clickStandaloneButton(string $buttonName): void
    {
        $buttons = $this->getHTMLPage()
            ->findAll($this->getLocator('menuButton'))
            ->filterBy(new ElementTextCriterion($buttonName));

        if ($buttons->any()) {
            $buttons->single()->click();

            return;
        }

        try {
            $this->getHTMLPage()->find($this->getLocator('moreButton'))->click();
        } catch (TimeoutException $e) {
            $this->getHTMLPage()
                ->findAll($this->getLocator('menuButton'))
                ->getByCriterion(new ElementTextCriterion($buttonName));
        }
        $this->getHTMLPage()
            ->findAll($this->getLocator('expandedMenuButton'))
            ->getByCriterion(new ElementTextCriterion($buttonName))->click();
    }

    private function clickButtonInGroup(string $groupName, string $buttonName): void
    {
        $group = $this->getHTMLPage()
            ->findAll($this->getLocator('splitButton'))
            ->filterBy(new ElementTextCriterion($groupName));

        if ($group->any()) {
            $group->single()->find($this->getLocator('toggle'))->click();

//            $this->setInteractiveBreakpoint(get_defined_vars());
            $this->getHTMLPage()->findAll($this->getLocator('button'))
                ->getByCriterion(new ElementTextCriterion($buttonName))
                ->click();

            return;
        }

        try {
            $this->getHTMLPage()->find($this->getLocator('moreButton'))->click();
        } catch (TimeoutException $e) {
            $this->getHTMLPage()
                ->findAll($this->getLocator('menuButton'))
                ->getByCriterion(new ElementTextCriterion($buttonName));
        }
        $this->getHTMLPage()
            ->findAll($this->getLocator('expandedMenuButton'))
            ->getByCriterion(new ElementTextCriterion($groupName))
            ->mouseOver();

        $this->getHTMLPage()
            ->findAll($this->getLocator('menuButton'))
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
        $this->showMoreButtonsIfNeeded();

        return $this->getHTMLPage()
            ->findAll($this->getLocator('menuButton'))
            ->filterBy(new ElementTextCriterion($buttonName))
            ->any();
    }

    private function showMoreButtonsIfNeeded(): void
    {
        $moreButton = $this->getHTMLPage()->findAll($this->getLocator('moreButton'));
        if ($moreButton->any()) {
            $moreButton->single()->click();
        }
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
            new VisibleCSSLocator('label', '.ibexa-btn__label'),
            new VisibleCSSLocator('menuButton', '.ibexa-context-menu .ibexa-btn'),
            new VisibleCSSLocator('button', '.ibexa-popup-menu__item-content'),
            new VisibleCSSLocator('toggle', '.ibexa-split-btn__toggle-btn '),
            new VisibleCSSLocator('splitButton', '.ibexa-split-btn'),
            new VisibleCSSLocator('moreButton', '.ibexa-context-menu__item--more'),
            new VisibleCSSLocator('expandedMenuButton', '.ibexa-popup-menu .ibexa-multilevel-popup-menu__item-content'),
        ];
    }
}
