<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Notifier;

use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\Notifications\Service\NotificationServiceInterface;
use Ibexa\Contracts\Notifications\Value\Notification\SymfonyNotificationAdapter;
use Ibexa\Contracts\Notifications\Value\Recipent\SymfonyRecipientAdapter;
use Ibexa\Contracts\Notifications\Value\Recipent\UserRecipient;
use Ibexa\Contracts\User\Notification\UserPasswordReset;
use Ibexa\Contracts\User\PasswordReset\NotifierInterface;
use Swift_Image;
use Swift_Mailer;
use Swift_Message;
use Twig\Environment;

class PasswordReset implements NotifierInterface
{
    private ConfigResolverInterface $configResolver;

    private Swift_Mailer $mailer;

    private Environment $twig;

    private NotificationServiceInterface $notificationService;

    private string $projectDir;

    public function __construct(
        ConfigResolverInterface $configResolver,
        Swift_Mailer $mailer,
        Environment $twig,
        NotificationServiceInterface $notificationService,
        string $projectDir
    ) {
        $this->configResolver = $configResolver;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->notificationService = $notificationService;
        $this->projectDir = $projectDir;
    }

    public function sendMessage(User $user, string $hashKey): void
    {
        if ($this->isNotifierConfigured()) {
            $this->sendNotification($user, $hashKey);

            return;
        }

        // Swiftmailer delivery has to be kept to maintain backwards compatibility
        $template = $this->twig->load($this->configResolver->getParameter('user_forgot_password.templates.mail'));

        $senderAddress = $this->configResolver->hasParameter('sender_address', 'swiftmailer.mailer')
            ? $this->configResolver->getParameter('sender_address', 'swiftmailer.mailer')
            : '';

        $subject = $template->renderBlock('subject');
        $from = $template->renderBlock('from') ?: $senderAddress;

        $message = (new Swift_Message())
            ->setSubject($subject)
            ->setTo($user->email);

        $embeddedHeader = $message->embed(Swift_Image::fromPath(
            $this->projectDir . '/public/bundles/ibexaadminui/img/mail/header.png'
        ));

        $body = $template->renderBlock('body', [
            'hash_key' => $hashKey,
            'header_img_path' => $embeddedHeader,
        ]);

        $message->setBody($body, 'text/html');

        if (empty($from) === false) {
            $message->setFrom($from);
        }

        $this->mailer->send($message);
    }

    private function sendNotification(User $user, string $token): void
    {
        $this->notificationService->send(
            new SymfonyNotificationAdapter(
                new UserPasswordReset($user, $token),
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
