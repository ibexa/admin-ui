<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Notifier;

use Ibexa\AdminUi\Notifier\MailImageEmbedder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\HttpKernel\KernelInterface;

final class MailImageEmbedderTest extends TestCase
{
    private const string MAIL_IMAGE_DIRECTORY = __DIR__ . '/../../../src/bundle/Resources/public/img/mail/';

    public function testEmbedsEveryImageAsCid(): void
    {
        $kernel = self::createStub(KernelInterface::class);
        $kernel->method('locateResource')->willReturnCallback(self::locateShippedImage(...));

        $email = new NotificationEmail();
        $context = (new MailImageEmbedder($kernel, new NullLogger()))->embed($email);

        self::assertSame(
            ['header_img_path', 'expiration_icon_path', 'expiration_icon_dark_path'],
            array_keys($context)
        );
        self::assertStringStartsWith('cid:header_', $context['header_img_path']);
        self::assertStringStartsWith('cid:clock_', $context['expiration_icon_path']);
        self::assertStringStartsWith('cid:clock-dark_', $context['expiration_icon_dark_path']);
        self::assertCount(3, $email->getAttachments());
    }

    public function testSkipsAndLogsMissingImage(): void
    {
        $kernel = self::createStub(KernelInterface::class);
        $kernel->method('locateResource')->willReturnCallback(
            static function (string $resource): string {
                if (str_ends_with($resource, 'clock-dark.png')) {
                    throw new InvalidArgumentException('Unable to find file');
                }

                return self::locateShippedImage($resource);
            }
        );

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('warning')
            ->with(
                self::stringContains('Unable to find file'),
                self::callback(
                    static fn (array $logContext): bool => str_ends_with($logContext['image_path'], 'clock-dark.png')
                )
            );

        $email = new NotificationEmail();
        $context = (new MailImageEmbedder($kernel, $logger))->embed($email);

        self::assertSame(['header_img_path', 'expiration_icon_path'], array_keys($context));
        self::assertCount(2, $email->getAttachments());
    }

    private static function locateShippedImage(string $resource): string
    {
        $path = self::MAIL_IMAGE_DIRECTORY . basename($resource);
        self::assertFileExists($path);

        return $path;
    }
}
