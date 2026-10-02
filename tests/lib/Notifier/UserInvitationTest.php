<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Notifier;

use DateTimeImmutable;
use Ibexa\AdminUi\Notifier\Notification;
use Ibexa\AdminUi\Notifier\UserInvitation;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\Notifications\Service\NotificationServiceInterface;
use Ibexa\Contracts\User\Invitation\Invitation;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

final class UserInvitationTest extends TestCase
{
    public function testSendsInvitationWhenNotificationIsSubscribed(): void
    {
        $notificationService = $this->createMock(NotificationServiceInterface::class);
        $notificationService->expects(self::once())->method('send');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('warning');

        $sender = $this->createSender(
            [Notification\UserInvitation::class => ['channels' => ['email']]],
            $notificationService,
            $logger
        );

        $sender->sendInvitation($this->createInvitation());
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
                ['notification' => Notification\UserInvitation::class]
            );

        $sender = $this->createSender([], $notificationService, $logger);

        $sender->sendInvitation($this->createInvitation());
    }

    /**
     * @param array<string, array{channels: array<string>}> $subscriptions
     */
    private function createSender(
        array $subscriptions,
        NotificationServiceInterface $notificationService,
        LoggerInterface $logger
    ): UserInvitation {
        $configResolver = $this->createMock(ConfigResolverInterface::class);
        $configResolver
            ->method('getParameter')
            ->with('notifications.subscriptions')
            ->willReturn($subscriptions);

        $sender = new UserInvitation(
            $this->createMock(Environment::class),
            $configResolver,
            $notificationService,
            $this->createMock(KernelInterface::class)
        );
        $sender->setLogger($logger);

        return $sender;
    }

    private function createInvitation(): Invitation
    {
        return new Invitation(
            'invitee@example.com',
            'hash',
            new DateTimeImmutable('2026-01-01 00:00:00'),
            'site',
            false
        );
    }
}
