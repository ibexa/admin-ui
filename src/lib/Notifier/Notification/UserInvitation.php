<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Notifier\Notification;

use DateInterval;
use Ibexa\AdminUi\Notifier\MailImageEmbedder;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\User\Invitation\Invitation;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Twig\Environment;

final class UserInvitation extends Notification implements EmailNotificationInterface
{
    private Invitation $invitation;

    private ConfigResolverInterface $configResolver;

    private Environment $twig;

    private MailImageEmbedder $mailImageEmbedder;

    public function __construct(
        Invitation $invitation,
        ConfigResolverInterface $configResolver,
        Environment $twig,
        MailImageEmbedder $mailImageEmbedder
    ) {
        $this->invitation = $invitation;
        $this->configResolver = $configResolver;
        $this->twig = $twig;
        $this->mailImageEmbedder = $mailImageEmbedder;

        parent::__construct();
    }

    public function asEmailMessage(
        EmailRecipientInterface $recipient,
        ?string $transport = null
    ): EmailMessage {
        $templatePath = $this->configResolver->getParameter(
            'user_invitation.templates.mail',
            null,
            $this->invitation->getSiteAccessIdentifier()
        );
        $template = $this->twig->load($templatePath);

        $subject = $template->renderBlock('subject');
        $from = $template->renderBlock('from') ?: null;
        $context = [
            'invite_hash' => $this->invitation->getHash(),
            'siteaccess' => $this->invitation->getSiteAccessIdentifier(),
            'invitation' => $this->invitation,
            'link_expiration' => new DateInterval($this->configResolver->getParameter(
                'user_invitation.hash_expiration_time',
                null,
                $this->invitation->getSiteAccessIdentifier()
            )),
            'support_email' => $this->configResolver->getParameter(
                'mail.support_email',
                null,
                $this->invitation->getSiteAccessIdentifier()
            ),
            'header_img_path' => '#',
        ];

        $email = NotificationEmail::asPublicEmail()
            ->htmlTemplate($templatePath)
            ->to($recipient->getEmail())
            ->subject($subject)
        ;

        $context = $this->mailImageEmbedder->embed($email) + $context;
        $email->context($context);

        if ($from !== null) {
            $email->from($from);
        }

        return new EmailMessage($email);
    }
}
