<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Notifier\Notification;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\User\Invitation\Invitation;
use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Twig\Environment;

final class UserInvitation extends Notification implements EmailNotificationInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private readonly Invitation $invitation;

    private readonly ConfigResolverInterface $configResolver;

    private readonly Environment $twig;

    private readonly KernelInterface $kernel;

    public function __construct(
        Invitation $invitation,
        ConfigResolverInterface $configResolver,
        Environment $twig,
        KernelInterface $kernel,
        ?LoggerInterface $logger = null
    ) {
        $this->invitation = $invitation;
        $this->configResolver = $configResolver;
        $this->twig = $twig;
        $this->kernel = $kernel;
        $this->logger = $logger ?? new NullLogger();

        parent::__construct();
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, ?string $transport = null): EmailMessage
    {
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
            'header_img_path' => '#',
        ];

        $email = NotificationEmail::asPublicEmail()
            ->htmlTemplate($templatePath)
            ->to($recipient->getEmail())
            ->subject($subject)
        ;

        try {
            $headerImagePath = $this->kernel->locateResource('@IbexaAdminUiBundle/Resources/public/img/mail/header.png');
            $cid = 'header_img_' . uniqid() . '@ibexa';
            $email->embedFromPath($headerImagePath, $cid);
            $context['header_img_path'] = 'cid:' . $cid;
        } catch (InvalidArgumentException $e) {
            $this->logger?->warning('Failed to load email header image: ' . $e->getMessage(), [
                'exception' => $e,
                'image_path' => '@IbexaAdminUiBundle/Resources/public/img/mail/header.png',
            ]);
        }
        $email->context($context);

        if ($from !== null) {
            $email->from($from);
        }

        return new EmailMessage($email);
    }
}
