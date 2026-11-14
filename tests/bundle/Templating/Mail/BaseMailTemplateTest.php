<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Templating\Mail;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Templating\GlobalHelper;
use JMS\TranslationBundle\Twig\TranslationExtension as DescriptionExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\IdentityTranslator;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

final class BaseMailTemplateTest extends TestCase
{
    private const string TEMPLATE = 'mail.html.twig';

    private const string SITE_ACCESS_SUPPORT_EMAIL = 'siteaccess@example.com';

    private Environment $twig;

    protected function setUp(): void
    {
        $configResolver = self::createStub(ConfigResolverInterface::class);
        $configResolver->method('getParameter')->willReturnCallback(
            static fn (string $name): string => $name === 'mail.support_email'
                ? self::SITE_ACCESS_SUPPORT_EMAIL
                : self::fail('Unexpected parameter: ' . $name)
        );

        $globalHelper = self::createStub(GlobalHelper::class);
        $globalHelper->method('getConfigResolver')->willReturn($configResolver);

        $themeLoader = new FilesystemLoader();
        $themeLoader->addPath(dirname(__DIR__, 4) . '/src/bundle/Resources/views/themes/admin', 'ibexadesign');

        $this->twig = new Environment(
            new ChainLoader([
                new ArrayLoader([
                    self::TEMPLATE => "{% extends '@ibexadesign/ui/mail/base_mail_template.html.twig' %}{% block subject %}Subject{% endblock %}",
                ]),
                $themeLoader,
            ]),
            ['strict_variables' => true]
        );
        $translator = new IdentityTranslator();
        $this->twig->addExtension(new TranslationExtension($translator));
        $this->twig->addExtension(new DescriptionExtension($translator));
        $this->twig->addGlobal('ibexa', $globalHelper);
    }

    /**
     * @return iterable<string, array{array<string, string>, string}>
     */
    public static function provideForTestSupportLineLinksTheAddress(): iterable
    {
        yield 'address from the context' => [['support_email' => 'context@example.com'], 'context@example.com'];
        yield 'absent from the context falls back to the SiteAccess setting' => [[], self::SITE_ACCESS_SUPPORT_EMAIL];
    }

    /**
     * @param array<string, string> $context
     */
    #[DataProvider('provideForTestSupportLineLinksTheAddress')]
    public function testSupportLineLinksTheAddress(
        array $context,
        string $expectedSupportEmail
    ): void {
        $html = $this->twig->render(self::TEMPLATE, $context);

        self::assertStringContainsString('href="mailto:' . $expectedSupportEmail . '"', $html);
        self::assertStringContainsString('mail.footer.support', $html);
        self::assertStringContainsString('mail.footer.copyright', $html);
    }

    public function testNullInTheContextHidesTheSupportLine(): void
    {
        $html = $this->twig->render(self::TEMPLATE, ['support_email' => null]);

        self::assertStringNotContainsString('mailto:', $html);
        self::assertStringNotContainsString('mail.footer.support', $html);
        self::assertStringContainsString('mail.footer.copyright', $html);
    }
}
