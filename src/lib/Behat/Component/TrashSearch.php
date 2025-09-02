<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

class TrashSearch extends Component
{
    public function submitSearchText(string $searchQuery): void
    {
        $this->getHTMLPage()->setTimeout(3)->find($this->getLocator('mainSearchBoxInput'))->setValue($searchQuery);
    }

    public function confirm(): void
    {
        $this->getHTMLPage()->setTimeout(3)->find($this->getLocator('mainSearchBoxConfirmButton'))->click();
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
        new VisibleCSSLocator('mainSearchBoxConfirmButton', '.ibexa-adaptive-filters__static-left .ibexa-input-text-wrapper--type-text .ibexa-input-text-wrapper__action-btn--search'),
            ];
    }
}
