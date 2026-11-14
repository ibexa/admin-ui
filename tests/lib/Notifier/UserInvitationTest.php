<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Notifier;

use DateTimeImmutable;
use Ibexa\AdminUi\Notifier\MailImageEmbedder;
use Ibexa\AdminUi\Notifier\Notification;
use Ibexa\AdminUi\Notifier\UserInvitation;
use Ibexa\Contracts\User\Invitation\Invitation;
use Psr\Log\NullLogger;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

final class UserInvitationTest extends AbstractNotifierTestCase
{
    public function testSendsInvitationWhenNotificationIsSubscribed(): void
    {
        $this->notificationService->expects($this->once())->method('send');
        $this->logger->expects($this->never())->method('error');

        $sender = $this->createSender(
            [Notification\UserInvitation::class => ['channels' => ['email']]]
        );

        $sender->sendInvitation($this->createInvitation());
    }

    public function testLogsWarningWhenNotificationIsNotSubscribed(): void
    {
        $this->notificationService->expects($this->never())->method('send');
        $this->mockLoggerError(Notification\UserInvitation::class);

        $sender = $this->createSender([]);

        $sender->sendInvitation($this->createInvitation());
    }

    /**
     * @param array<string, array{channels: array<string>}> $subscriptions
     */
    private function createSender(array $subscriptions): UserInvitation
    {
        $this->mockConfigResolverGetParameter($subscriptions);

        $sender = new UserInvitation(
            $this->createMock(Environment::class),
            $this->configResolver,
            $this->notificationService,
            new MailImageEmbedder(self::createStub(KernelInterface::class), new NullLogger())
        );
        $sender->setLogger($this->logger);

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
