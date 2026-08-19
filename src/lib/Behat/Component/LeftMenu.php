<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\CSSLocator;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

final class LeftMenu extends Component
{
    public function goToTab(string $tabName): void
    {
        $this->ensureMenuIsExpanded();

        $this->getHTMLPage()
            ->setTimeout(5)
            ->findAll($this->getLocator('topLevelItemLabel'))
            ->getByCriterion(new ElementTextCriterion($tabName))
            ->click();
    }

    public function goToSubTab(string $tabName, string $subTabName): void
    {
        $this->ensureMenuIsExpanded();

        $isTabAccordionExpanded = $this->getHTMLPage()
            ->setTimeout(0)
            ->findAll($this->getLocator('expandedAccordionTriggerLabel'))
            ->filterBy(new ElementTextCriterion($tabName))
            ->any();

        if (!$isTabAccordionExpanded) {
            $this->goToTab($tabName);
        }

        $this->changeSubTab($subTabName);
    }

    public function changeSubTab(string $subTabName): void
    {
        $this->ensureMenuIsExpanded();

        $this->getHTMLPage()
            ->setTimeout(5)
            ->findAll($this->getLocator('secondLevelItemLabel'))
            ->getByCriterion(new ElementTextCriterion($subTabName))
            ->click();
    }

    public function toggleMenu(): void
    {
        $this->getHTMLPage()->find($this->getLocator('menuExpandToggler'))->click();
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->find($this->getLocator('menuSelector'))->assert()->isVisible();
    }

    private function ensureMenuIsExpanded(): void
    {
        $isCollapsed = $this->getHTMLPage()
            ->setTimeout(0)
            ->findAll($this->getLocator('collapsedNavbar'))
            ->any();

        if ($isCollapsed) {
            $this->getHTMLPage()->find($this->getLocator('menuExpandToggler'))->click();
            $this->getHTMLPage()
                ->setTimeout(5)
                ->find($this->getLocator('topLevelItemLabel'))
                ->assert()->isVisible();
        }
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('menuSelector', '.ibexa-main-menu'),
            new CSSLocator('collapsedNavbar', '.ibexa-main-menu__navbar--collapsed'),
            new VisibleCSSLocator('menuExpandToggler', '.ibexa-main-menu__expand-toggler'),
            new VisibleCSSLocator(
                'topLevelItemLabel',
                '.ibexa-main-menu__items-list--primary > .ibexa-main-menu__item > .ibexa-main-menu__item-action .ibexa-main-menu__item-text-column, .ibexa-main-menu__items-list--secondary > .ibexa-main-menu__item > .ibexa-main-menu__item-action .ibexa-main-menu__item-text-column, .ibexa-main-menu__item-action--accordion-trigger .ibexa-main-menu__item-text-column'
            ),
            new VisibleCSSLocator('secondLevelItemLabel', '.ibexa-main-menu__item-action--second-level-link .ibexa-main-menu__item-text-column'),
            new VisibleCSSLocator('expandedAccordionTriggerLabel', '.ids-accordion--is-expanded .ibexa-main-menu__item-action--accordion-trigger .ibexa-main-menu__item-text-column'),
            new VisibleCSSLocator('dashboardIcon', '.ibexa-main-header__brand'),
        ];
    }
}
