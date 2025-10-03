<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

class CancelContentDialog extends Dialog
{
    public function confirmCanceling(string $cancelScheduledHidingButton): void
    {
        $this->getHTMLPage()
            ->findAll($this->getLocator('confirmCancelButton'))
            ->getByCriterion(new ElementTextCriterion($cancelScheduledHidingButton))
            ->click();
    }

    public function specifyLocators(): array
    {
        return array_merge(parent::specifyLocators(), [
            new VisibleCSSLocator('confirmCancelButton', '#review-content-modal .ibexa-btn'),
        ]);
    }
}
