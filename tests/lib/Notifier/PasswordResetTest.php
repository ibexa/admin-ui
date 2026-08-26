<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Notifier;

use Ibexa\AdminUi\Notifier\Notification\UserPasswordReset;
use Ibexa\AdminUi\Notifier\PasswordReset;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\Notifications\Service\NotificationServiceInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

final class PasswordResetTest extends TestCase
{
    public function testSendsMessageWhenNotificationIsSubscribed(): void
    {
        $notificationService = $this->createMock(NotificationServiceInterface::class);
        $notificationService->expects(self::once())->method('send');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        $notifier = $this->createNotifier(
            [UserPasswordReset::class => ['channels' => ['email']]],
            $notificationService,
            $logger
        );

        $notifier->sendMessage($this->createMock(User::class), 'hash-key');
    }

    public function testLogsWarningWhenNotificationIsNotSubscribed(): void
    {
        $notificationService = $this->createMock(NotificationServiceInterface::class);
        $notificationService->expects(self::never())->method('send');

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('warning')
            ->with(
                self::stringContains('notifier.subscriptions'),
                ['notification' => UserPasswordReset::class]
            );

        $notifier = $this->createNotifier([], $notificationService, $logger);

        $notifier->sendMessage($this->createMock(User::class), 'hash-key');
    }

    /**
     * @param array<string, array{channels: array<string>}> $subscriptions
     */
    private function createNotifier(
        array $subscriptions,
        NotificationServiceInterface $notificationService,
        LoggerInterface $logger
    ): PasswordReset {
        $configResolver = $this->createMock(ConfigResolverInterface::class);
        $configResolver
            ->method('getParameter')
            ->with('notifications.subscriptions')
            ->willReturn($subscriptions);

        $notifier = new PasswordReset(
            $configResolver,
            $this->createMock(Environment::class),
            $notificationService,
            $this->createMock(KernelInterface::class)
        );
        $notifier->setLogger($logger);

        return $notifier;
    }
}
