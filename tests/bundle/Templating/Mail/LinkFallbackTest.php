<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Templating\Mail;

use JMS\TranslationBundle\Twig\TranslationExtension as DescriptionExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class LinkFallbackTest extends TestCase
{
    private const string TEMPLATE = 'link_fallback.html.twig';

    private const string URL = 'https://example.com/reset?hash=abc';

    private Environment $twig;

    protected function setUp(): void
    {
        $resourcesDir = dirname(__DIR__, 4) . '/src/bundle/Resources';

        $translator = new Translator('en');
        $translator->addLoader('xliff', new XliffFileLoader());
        $translator->addResource('xliff', $resourcesDir . '/translations/ibexa_mail.en.xliff', 'en', 'ibexa_mail');

        $this->twig = new Environment(
            new FilesystemLoader($resourcesDir . '/views/themes/admin/ui/mail/components'),
            ['strict_variables' => true]
        );
        $this->twig->addExtension(new TranslationExtension($translator));
        $this->twig->addExtension(new DescriptionExtension($translator));
    }

    public function testUrlIsRenderedOnlyInsideColouredLinks(): void
    {
        $html = $this->twig->render(self::TEMPLATE, ['url' => self::URL]);

        self::assertMatchesRegularExpression(
            '~<a[^>]*style="[^"]*color: #4e24a5;[^"]*"[^>]*href="' . preg_quote(self::URL, '~') . '"[^>]*>\s*Open in browser\s*</a>~',
            $html
        );
        self::assertMatchesRegularExpression(
            '~<a[^>]*style="[^"]*color: #4e24a5;[^"]*"[^>]*href="' . preg_quote(self::URL, '~') . '"[^>]*>\[' . preg_quote(self::URL, '~') . '\]</a>~',
            $html
        );
        self::assertSame(
            3,
            substr_count($html, self::URL),
            'The URL is expected twice as href and once as link text, never as plain text (mail clients auto-link it in their own colour)'
        );
    }
}
