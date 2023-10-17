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
use PHPUnit\Framework\Assert;

class Notification extends Component
{
    private const TIMEOUT = 30;

    public function verifyAlertSuccess(): void
    {
        $this->getHTMLPage()
            ->setTimeout(self::TIMEOUT)
            ->find($this->getLocator('successAlert'))
            ->assert()
            ->isVisible();
    }

    public function verifyAlertWarning(): void
    {
        $this->getHTMLPage()
            ->setTimeout(self::TIMEOUT)
            ->find($this->getLocator('warningAlert'))
            ->assert()
            ->isVisible();
    }

    public function verifyAlertFailure(): void
    {
        Assert::assertTrue(
            $this->getHTMLPage()
                ->setTimeout(self::TIMEOUT)
                ->find($this->getLocator('failureAlert'))
                ->isVisible(),
            'Failure alert not found.'
        );
    }

    public function getMessage(): string
    {
        return $this->getHTMLPage()->setTimeout(self::TIMEOUT)->find($this->getLocator('alertMessage'))->getText();
    }

    public function closeAlert(): void
    {
        $closeButtons = $this->getHTMLPage()->findAll($this->getLocator('closeAlert'));

        foreach ($closeButtons as $closeButton) {
            $closeButton->click();
        }
    }

    public function isVisible(): bool
    {
        return $this->getHTMLPage()->findAll($this->getLocator('alert'))->any();
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()
            ->setTimeout(self::TIMEOUT)
            ->waitUntilCondition(
                new ElementExistsCondition($this->getHTMLPage(), $this->getLocator('alert'))
            );
    }

    public function verifyMessage(string $expectedMessage)
    {
        $this->getHTMLPage()->setTimeout(self::TIMEOUT)->find($this->getLocator('alertMessage'))->assert()->textEquals($expectedMessage);
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('alert', '.ibexa-notifications-container .alert'),
            new VisibleCSSLocator('alertMessage', '.ibexa-notifications-container .ibexa-alert__title'),
            new VisibleCSSLocator('successAlert', '.ibexa-alert--success'),
            new VisibleCSSLocator('warningAlert', '.ibexa-alert--warning'),
            new VisibleCSSLocator('failureAlert', '.ibexa-alert--error'),
            new VisibleCSSLocator('closeAlert', '.ibexa-alert__close-btn'),
        ];
    }
}
