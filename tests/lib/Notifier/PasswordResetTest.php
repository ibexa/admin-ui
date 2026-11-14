<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Notifier;

use Ibexa\AdminUi\Notifier\MailImageEmbedder;
use Ibexa\AdminUi\Notifier\Notification\UserPasswordReset;
use Ibexa\AdminUi\Notifier\PasswordReset;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Psr\Log\NullLogger;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

final class PasswordResetTest extends AbstractNotifierTestCase
{
    public function testSendsMessageWhenNotificationIsSubscribed(): void
    {
        $this->notificationService->expects($this->once())->method('send');
        $this->logger->expects($this->never())->method('error');

        $notifier = $this->createNotifier([UserPasswordReset::class => ['channels' => ['email']]]);

        $notifier->sendMessage($this->createMock(User::class), 'hash-key');
    }

    public function testLogsWarningWhenNotificationIsNotSubscribed(): void
    {
        $this->notificationService->expects($this->never())->method('send');
        $this->mockLoggerError(UserPasswordReset::class);

        $notifier = $this->createNotifier([]);

        $notifier->sendMessage($this->createMock(User::class), 'hash-key');
    }

    /**
     * @param array<string, array{channels: array<string>}> $subscriptions
     */
    private function createNotifier(array $subscriptions): PasswordReset
    {
        $this->mockConfigResolverGetParameter($subscriptions);

        $notifier = new PasswordReset(
            $this->configResolver,
            $this->createMock(Environment::class),
            $this->notificationService,
            new MailImageEmbedder(self::createStub(KernelInterface::class), new NullLogger())
        );
        $notifier->setLogger($this->logger);

        return $notifier;
    }
}
