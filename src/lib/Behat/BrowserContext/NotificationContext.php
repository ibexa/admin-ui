<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Ibexa\AdminUi\Behat\Component\Notification;
use Ibexa\Behat\Core\Behat\ArgumentParser;
use PHPUnit\Framework\Assert;

/** Context for actions on notifications */
class NotificationContext implements Context
{
    private Notification $notification;

    private ArgumentParser $argumentParser;

    public function __construct(Notification $notification, ArgumentParser $argumentParser)
    {
        $this->notification = $notification;
        $this->argumentParser = $argumentParser;
    }

    /**
     * @Then notification that :itemType :itemName is :action appears
     */
    public function notificationAppears(string $itemType, string $itemName, string $action): void
    {
        $expectedMessage = sprintf('%s \'%s\' %s.', $itemType, $itemName, $action);

        $this->notification->verifyIsLoaded();
        $this->notification->verifyAlertSuccess();
        Assert::assertEquals($expectedMessage, $this->notification->getMessage());
        $this->notification->closeAlert();
    }

    /**
     * @Then warning notification that :message appears
     */
    public function warningNotificationAppears(string $message): void
    {
        $this->notification->verifyIsLoaded();
        $this->notification->verifyAlertWarning();
        $this->notification->verifyMessage($message);
        $this->notification->closeAlert();
    }

    /**
     * @Then success notification that :message appears
     */
    public function specificNotificationAppears(string $message): void
    {
        $this->notification->verifyIsLoaded();
        $this->notification->verifyAlertSuccess();
        $this->notification->verifyMessage($message);
        $this->notification->closeAlert();
    }

    /**
     * @Then error notification that :message appears
     */
    public function specificErrorNotificationAppears(string $message): void
    {
        $this->notification->verifyIsLoaded();
        $this->notification->verifyAlertFailure();
        Assert::assertStringContainsString($message, $this->notification->getMessage());
        $this->notification->closeAlert();
    }
}
