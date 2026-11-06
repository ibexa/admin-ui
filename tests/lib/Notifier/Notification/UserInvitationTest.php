<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Notifier\Notification;

use DateInterval;
use DateTimeImmutable;
use Ibexa\AdminUi\Notifier\MailImageEmbedder;
use Ibexa\AdminUi\Notifier\Notification\UserInvitation;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\User\Invitation\Invitation;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class UserInvitationTest extends TestCase
{
    private const string TEMPLATE_PATH = 'invitation/mail.html.twig';

    private const string MAIL_IMAGE_DIRECTORY = __DIR__ . '/../../../../src/bundle/Resources/public/img/mail/';

    public function testEmailReadsSettingsOfTheInvitationSiteAccess(): void
    {
        $invitation = new Invitation('jane@example.com', 'hash', new DateTimeImmutable(), 'site', false);

        $configResolver = self::createStub(ConfigResolverInterface::class);
        $configResolver->method('getParameter')->willReturnCallback(
            static function (
                string $name,
                ?string $namespace,
                ?string $scope
            ): string {
                self::assertSame('site', $scope);

                return match ($name) {
                    'user_invitation.templates.mail' => self::TEMPLATE_PATH,
                    'user_invitation.hash_expiration_time' => 'P2D',
                    'mail.support_email' => 'helpdesk@example.com',
                    default => self::fail('Unexpected parameter: ' . $name),
                };
            }
        );

        $kernel = self::createStub(KernelInterface::class);
        $kernel->method('locateResource')->willReturnCallback(
            static fn (string $resource): string => self::MAIL_IMAGE_DIRECTORY . basename($resource)
        );

        $notification = new UserInvitation(
            $invitation,
            $configResolver,
            new Environment(new ArrayLoader([
                self::TEMPLATE_PATH => '{% block subject %}You are invited{% endblock %}{% block from %}{% endblock %}',
            ])),
            new MailImageEmbedder($kernel, new NullLogger())
        );

        $email = $notification->asEmailMessage(new Recipient('jane@example.com'))->getMessage();

        self::assertInstanceOf(NotificationEmail::class, $email);
        self::assertSame(self::TEMPLATE_PATH, $email->getHtmlTemplate());
        self::assertSame('You are invited', $email->getSubject());
        self::assertSame('jane@example.com', $email->getTo()[0]->getAddress());

        $context = $email->getContext();
        self::assertSame('hash', $context['invite_hash']);
        self::assertSame('site', $context['siteaccess']);
        self::assertSame($invitation, $context['invitation']);
        self::assertEquals(new DateInterval('P2D'), $context['link_expiration']);
        self::assertSame('helpdesk@example.com', $context['support_email']);
        self::assertStringStartsWith('cid:header_', $context['header_img_path']);
        self::assertStringStartsWith('cid:clock_', $context['expiration_icon_path']);
        self::assertStringStartsWith('cid:clock-dark_', $context['expiration_icon_dark_path']);
        self::assertCount(3, $email->getAttachments());
    }
}
