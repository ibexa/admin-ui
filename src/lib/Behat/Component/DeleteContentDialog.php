<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

class DeleteContentDialog extends Dialog
{
    public function confirmTrashing(): void
    {
        $this->getHTMLPage()->find($this->getLocator('trashConfirmCheckbox'))->click();
    }

    public function specifyLocators(): array
    {
        return array_merge(parent::specifyLocators(), [
            new VisibleCSSLocator('trashConfirmCheckbox', '.modal-content .ibexa-input--checkbox'),
        ]);
    }
}
