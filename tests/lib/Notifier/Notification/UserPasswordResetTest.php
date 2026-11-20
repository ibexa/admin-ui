<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Notifier\Notification;

use DateInterval;
use Ibexa\AdminUi\Notifier\MailImageEmbedder;
use Ibexa\AdminUi\Notifier\Notification\UserPasswordReset;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class UserPasswordResetTest extends TestCase
{
    private const string TEMPLATE_PATH = 'forgot_password/mail.html.twig';

    private const string MAIL_IMAGE_DIRECTORY = __DIR__ . '/../../../../src/bundle/Resources/public/img/mail/';

    private User $user;

    private ConfigResolverInterface $configResolver;

    protected function setUp(): void
    {
        $this->user = self::createStub(User::class);
        $this->user->method('getName')->willReturn('John Doe');

        $this->configResolver = self::createStub(ConfigResolverInterface::class);
        $this->configResolver->method('getParameter')->willReturnCallback(
            static fn (string $name): string => match ($name) {
                'user_forgot_password.templates.mail' => self::TEMPLATE_PATH,
                'security.token_interval_spec' => 'PT1H',
                'mail.support_email' => 'helpdesk@example.com',
                default => self::fail('Unexpected parameter: ' . $name),
            }
        );
    }

    public function testEmailCarriesTemplateContextAndImages(): void
    {
        $notification = new UserPasswordReset(
            $this->user,
            'token',
            $this->configResolver,
            $this->createTwig('{% block subject %}Reset your password{% endblock %}{% block from %}noreply@example.com{% endblock %}'),
            $this->createMailImageEmbedder(false)
        );

        $email = $notification->asEmailMessage(new Recipient('john@example.com'))->getMessage();

        self::assertInstanceOf(NotificationEmail::class, $email);
        self::assertSame(self::TEMPLATE_PATH, $email->getHtmlTemplate());
        self::assertSame('Reset your password', $email->getSubject());
        self::assertSame('john@example.com', $email->getTo()[0]->getAddress());
        self::assertSame('noreply@example.com', $email->getFrom()[0]->getAddress());

        $context = $email->getContext();
        self::assertSame('token', $context['hash_key']);
        self::assertSame('John Doe', $context['user_name']);
        self::assertEquals(new DateInterval('PT1H'), $context['link_expiration']);
        self::assertSame('helpdesk@example.com', $context['support_email']);
        self::assertStringStartsWith('cid:header_', $context['header_img_path']);
        self::assertStringStartsWith('cid:clock_', $context['expiration_icon_path']);
        self::assertStringStartsWith('cid:clock-dark_', $context['expiration_icon_dark_path']);
        self::assertCount(3, $email->getAttachments());
    }

    public function testMissingImagesLeaveTemplateFallbacks(): void
    {
        $notification = new UserPasswordReset(
            $this->user,
            'token',
            $this->configResolver,
            $this->createTwig('{% block subject %}Reset your password{% endblock %}{% block from %}{% endblock %}'),
            $this->createMailImageEmbedder(true)
        );

        $email = $notification->asEmailMessage(new Recipient('john@example.com'))->getMessage();

        self::assertInstanceOf(NotificationEmail::class, $email);
        self::assertSame([], $email->getFrom());

        $context = $email->getContext();
        self::assertSame('#', $context['header_img_path']);
        self::assertArrayNotHasKey('expiration_icon_path', $context);
        self::assertArrayNotHasKey('expiration_icon_dark_path', $context);
        self::assertCount(0, $email->getAttachments());
    }

    private function createTwig(string $template): Environment
    {
        return new Environment(new ArrayLoader([self::TEMPLATE_PATH => $template]));
    }

    private function createMailImageEmbedder(bool $imagesMissing): MailImageEmbedder
    {
        $kernel = self::createStub(KernelInterface::class);
        if ($imagesMissing) {
            $kernel->method('locateResource')->willThrowException(new InvalidArgumentException('Unable to find file'));
        } else {
            $kernel->method('locateResource')->willReturnCallback(
                static fn (string $resource): string => self::MAIL_IMAGE_DIRECTORY . basename($resource)
            );
        }

        return new MailImageEmbedder($kernel, new NullLogger());
    }
}
