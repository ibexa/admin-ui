<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\Condition\ElementExistsCondition;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

final class FullView extends Component
{
    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->setTimeout(10)->waitUntilCondition(
            new ElementExistsCondition($this->getHTMLPage(), $this->getLocator('locationFullPreview'))
        );
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('locationFullPreview', '.ibexa-sc-location-full-preview--loaded'),
        ];
    }
}
