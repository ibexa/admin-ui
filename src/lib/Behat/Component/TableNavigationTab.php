<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextFragmentCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use PHPUnit\Framework\Assert;

class TableNavigationTab extends Component
{
    public function getActiveTabName(): string
    {
        return $this->getHTMLPage()->find($this->getLocator('activeNavLink'))->getText();
    }

    public function goToTab(string $tabName): void
    {
        if ($this->getActiveTabName() == $tabName) {
            return;
        } else {
            $tab = $this->getHTMLPage()->setTimeout(3)
            ->findAll($this->getLocator('navLink'))
            ->getByCriterion(new ElementTextFragmentCriterion($tabName));
            $tab->click();
        }
    }

    public function verifyIsLoaded(): void
    {
        Assert::assertTrue($this->getHTMLPage()->find($this->getLocator('activeNavLink'))->isVisible());
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('activeNavLink', '.ibexa-tabs__tab--active'),
            new VisibleCSSLocator('navLink', '.ibexa-tabs__tab'),
        ];
    }
}
