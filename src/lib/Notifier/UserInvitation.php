<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Notifier;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\Notifications\Service\NotificationServiceInterface;
use Ibexa\Contracts\Notifications\Value\Notification\SymfonyNotificationAdapter;
use Ibexa\Contracts\Notifications\Value\Recipent\SymfonyRecipientAdapter;
use Ibexa\Contracts\User\Invitation\Invitation;
use Ibexa\Contracts\User\Invitation\InvitationSender;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Twig\Environment;

final class UserInvitation implements InvitationSender, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private Environment $twig;

    private ConfigResolverInterface $configResolver;

    private KernelInterface $kernel;

    private NotificationServiceInterface $notificationService;

    public function __construct(
        Environment $twig,
        ConfigResolverInterface $configResolver,
        NotificationServiceInterface $notificationService,
        KernelInterface $kernel
    ) {
        $this->twig = $twig;
        $this->configResolver = $configResolver;
        $this->kernel = $kernel;
        $this->notificationService = $notificationService;
    }

    public function sendInvitation(Invitation $invitation): void
    {
        if ($this->isNotifierConfigured()) {
            $this->sendNotification($invitation);
        }
    }

    private function sendNotification(Invitation $invitation): void
    {
        $this->notificationService->send(
            new SymfonyNotificationAdapter(
                new Notification\UserInvitation(
                    $invitation,
                    $this->configResolver,
                    $this->twig,
                    $this->kernel,
                    $this->logger
                ),
            ),
            [new SymfonyRecipientAdapter(new Recipient($invitation->getEmail()))],
        );
    }

    private function isNotifierConfigured(): bool
    {
        $subscriptions = $this->configResolver->getParameter('notifications.subscriptions');

        return array_key_exists(Notification\UserInvitation::class, $subscriptions)
            && !empty($subscriptions[Notification\UserInvitation::class]['channels']);
    }
}
