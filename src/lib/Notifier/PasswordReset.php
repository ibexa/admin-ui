<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Notifier;

use Ibexa\AdminUi\Notifier\Notification\UserPasswordReset;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\Notifications\Service\NotificationServiceInterface;
use Ibexa\Contracts\Notifications\Value\Notification\SymfonyNotificationAdapter;
use Ibexa\Contracts\Notifications\Value\Recipent\SymfonyRecipientAdapter;
use Ibexa\Contracts\Notifications\Value\Recipent\UserRecipient;
use Ibexa\Contracts\User\PasswordReset\NotifierInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class PasswordReset implements NotifierInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ConfigResolverInterface $configResolver;

    private Environment $twig;

    private NotificationServiceInterface $notificationService;

    private KernelInterface $kernel;

    public function __construct(
        ConfigResolverInterface $configResolver,
        Environment $twig,
        NotificationServiceInterface $notificationService,
        KernelInterface $kernel
    ) {
        $this->configResolver = $configResolver;
        $this->twig = $twig;
        $this->notificationService = $notificationService;
        $this->kernel = $kernel;
    }

    public function sendMessage(User $user, string $hashKey): void
    {
        if ($this->isNotifierConfigured()) {
            $this->sendNotification($user, $hashKey);
        }
    }

    private function sendNotification(User $user, string $token): void
    {
        $this->notificationService->send(
            new SymfonyNotificationAdapter(
                new UserPasswordReset(
                    $user,
                    $token,
                    $this->configResolver,
                    $this->twig,
                    $this->kernel,
                    $this->logger
                ),
            ),
            [new SymfonyRecipientAdapter(new UserRecipient($user))],
        );
    }

    private function isNotifierConfigured(): bool
    {
        $subscriptions = $this->configResolver->getParameter('notifications.subscriptions');

        return array_key_exists(UserPasswordReset::class, $subscriptions)
            && !empty($subscriptions[UserPasswordReset::class]['channels']);
    }
}
