<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Notifier\Notification;

use DateInterval;
use Ibexa\AdminUi\Notifier\MailImageEmbedder;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\User\Notification\UserAwareNotificationInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Twig\Environment;

final class UserPasswordReset extends Notification implements EmailNotificationInterface, UserAwareNotificationInterface
{
    private User $user;

    private string $token;

    private ConfigResolverInterface $configResolver;

    private Environment $twig;

    private MailImageEmbedder $mailImageEmbedder;

    public function __construct(
        User $user,
        string $token,
        ConfigResolverInterface $configResolver,
        Environment $twig,
        MailImageEmbedder $mailImageEmbedder
    ) {
        $this->user = $user;
        $this->token = $token;
        $this->configResolver = $configResolver;
        $this->twig = $twig;
        $this->mailImageEmbedder = $mailImageEmbedder;

        parent::__construct();
    }

    public function asEmailMessage(
        EmailRecipientInterface $recipient,
        ?string $transport = null
    ): EmailMessage {
        $templatePath = $this->configResolver->getParameter('user_forgot_password.templates.mail');
        $template = $this->twig->load($templatePath);

        $subject = $template->renderBlock('subject');
        $from = $template->renderBlock('from') ?: null;

        $email = NotificationEmail::asPublicEmail()
            ->to($recipient->getEmail())
            ->subject($subject)
        ;
        $context = [
            'hash_key' => $this->token,
            'user_name' => $this->user->getName(),
            'link_expiration' => new DateInterval($this->configResolver->getParameter('security.token_interval_spec')),
            'support_email' => $this->configResolver->getParameter('mail.support_email'),
            'header_img_path' => '#',
        ];

        $context = $this->mailImageEmbedder->embed($email) + $context;
        $email->htmlTemplate($templatePath);
        $email->context($context);

        if ($from !== null) {
            $email->from($from);
        }

        return new EmailMessage($email);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
