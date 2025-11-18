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
use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Swift_Image;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Twig\Environment;

final class UserInvitation implements InvitationSender, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private Environment $twig;

    private ConfigResolverInterface $configResolver;

    private Swift_Mailer $mailer;

    private KernelInterface $kernel;

    private NotificationServiceInterface $notificationService;

    public function __construct(
        Environment $twig,
        ConfigResolverInterface $configResolver,
        Swift_Mailer $mailer,
        NotificationServiceInterface $notificationService,
        KernelInterface $kernel
    ) {
        $this->twig = $twig;
        $this->configResolver = $configResolver;
        $this->mailer = $mailer;
        $this->kernel = $kernel;
        $this->notificationService = $notificationService;
    }

    private function locateMailImage(string $imageName): string
    {
        try {
            return $this->kernel->locateResource('@IbexaAdminUiBundle/Resources/public/img/mail/' . $imageName);
        } catch (InvalidArgumentException $e) {
            if ($this->logger) {
                $this->logger->error('Failed to locate mail image: ' . $imageName, ['exception' => $e]);
            }

            return '#';
        }
    }

    public function sendInvitation(Invitation $invitation): void
    {
        if ($this->isNotifierConfigured()) {
            $this->sendNotification($invitation);

            return;
        }

        $template = $this->twig->load(
            $this->configResolver->getParameter(
                'user_invitation.templates.mail',
                null,
                $invitation->getSiteAccessIdentifier()
            )
        );

        $senderAddress = $this->configResolver->hasParameter('sender_address', 'swiftmailer.mailer')
            ? $this->configResolver->getParameter('sender_address', 'swiftmailer.mailer')
            : '';

        $subject = $template->renderBlock('subject');
        $from = $template->renderBlock('from') ?: $senderAddress;

        $message = (new Swift_Message())
            ->setSubject($subject)
            ->setTo($invitation->getEmail());

        $embeddedHeader = $message->embed(Swift_Image::fromPath($this->locateMailImage('header.jpg')));

        $body = $template->renderBlock('body', [
            'invite_hash' => $invitation->getHash(),
            'siteaccess' => $invitation->getSiteAccessIdentifier(),
            'invitation' => $invitation,
            'header_img_path' => $embeddedHeader,
        ]);

        $message->setBody($body, 'text/html');

        if (empty($from) === false) {
            $message->setFrom($from);
        }

        $this->mailer->send($message);
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
