<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Templating\Mail;

use DateInterval;
use JMS\TranslationBundle\Twig\TranslationExtension as DescriptionExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class LinkExpirationTest extends TestCase
{
    private const string TEMPLATE = 'link_expiration.html.twig';

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

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideForTestDurationIsHumanReadable(): iterable
    {
        yield 'whole days' => ['P2D', '2 days'];
        yield 'one day' => ['P1D', '1 day'];
        yield 'hours summing up to a day' => ['PT24H', '1 day'];
        yield 'calendar month' => ['P1M', '31 days'];
        yield 'hours not summing up to days' => ['PT36H', '36 hours'];
        yield 'one hour' => ['PT1H', '1 hour'];
        yield 'minutes not summing up to hours' => ['PT90M', '90 minutes'];
        yield 'one minute' => ['PT1M', '1 minute'];
        yield 'seconds round up to a minute' => ['PT61S', '2 minutes'];
        yield 'less than a minute' => ['PT30S', '1 minute'];
        yield 'zero' => ['PT0S', '1 minute'];
    }

    #[DataProvider('provideForTestDurationIsHumanReadable')]
    public function testDurationIsHumanReadable(
        string $intervalSpec,
        string $expectedDuration
    ): void {
        $html = $this->render($intervalSpec);

        self::assertMatchesRegularExpression(
            '~This link expires in ' . preg_quote($expectedDuration, '~') . '\s*</td>~',
            $html
        );
        self::assertStringNotContainsString('<img', $html);
    }

    public function testIconsAreRenderedWhenProvided(): void
    {
        $html = $this->render('PT1H', 'cid:clock@ibexa', 'cid:clock-dark@ibexa');

        self::assertStringContainsString('class="ibexa-email-icon ibexa-email-icon--light"', $html);
        self::assertStringContainsString('src="cid:clock@ibexa"', $html);
        self::assertStringContainsString('class="ibexa-email-icon ibexa-email-icon--dark"', $html);
        self::assertStringContainsString('src="cid:clock-dark@ibexa"', $html);
    }

    private function render(
        string $intervalSpec,
        ?string $iconPath = null,
        ?string $iconDarkPath = null
    ): string {
        return $this->twig->render(self::TEMPLATE, [
            'interval' => new DateInterval($intervalSpec),
            'icon_path' => $iconPath,
            'icon_dark_path' => $iconDarkPath,
        ]);
    }
}
