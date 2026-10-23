<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Notifier;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\Notifications\Service\NotificationServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

abstract class AbstractNotifierTestCase extends TestCase
{
    protected MockObject & ConfigResolverInterface $configResolver;

    protected MockObject & NotificationServiceInterface $notificationService;

    protected MockObject & LoggerInterface $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->notificationService = $this->createMock(NotificationServiceInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * @param array<string, array{channels: array<string>}> $subscriptions
     */
    protected function mockConfigResolverGetParameter(array $subscriptions): void
    {
        $this->configResolver
            ->expects(self::once())
            ->method('getParameter')
            ->with('notifications.subscriptions')
            ->willReturn($subscriptions);
    }

    /**
     * @param class-string $notification
     */
    protected function mockLoggerWarning(string $notification): void
    {
        $this->logger
            ->expects(self::once())
            ->method('warning')
            ->with(
                self::stringContains('notifier.subscriptions'),
                ['notification' => $notification]
            );
    }
}
