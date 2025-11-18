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

    public function __construct(
        private readonly Environment $twig,
        private readonly ConfigResolverInterface $configResolver,
        private readonly NotificationServiceInterface $notificationService,
        private readonly KernelInterface $kernel
    ) {
    }

    public function sendInvitation(Invitation $invitation): void
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
}
