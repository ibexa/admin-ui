<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\Criterion\ChildElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

class PropertiesList extends Component
{
    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->find($this->getLocator('tabContent'))->assert()->isVisible();
    }

    public function verifyValue(string $label, string $value): void
    {
        $this->getHTMLPage()
                ->findAll($this->getLocator('globalPropertiesItem'))
                ->getByCriterion(new ChildElementTextCriterion($this->getLocator('globalPropertiesLabel'), $label))
                ->find($this->getLocator('globalPropertiesValue'))
                ->assert()->textEquals($value);
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('tabContent', '.ibexa-tab-content'),
            new VisibleCSSLocator('globalPropertiesItem', '.ibexa-details__item'),
            new VisibleCSSLocator('globalPropertiesLabel', '.ibexa-details__item-label'),
            new VisibleCSSLocator('globalPropertiesValue', '.ibexa-details__item-content'),
        ];
    }
}
