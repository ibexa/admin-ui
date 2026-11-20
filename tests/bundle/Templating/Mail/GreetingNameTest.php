<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Templating\Mail;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class GreetingNameTest extends TestCase
{
    private const string TEMPLATE = 'greeting_name.html.twig';

    private Environment $twig;

    protected function setUp(): void
    {
        $this->twig = new Environment(
            new FilesystemLoader(dirname(__DIR__, 4) . '/src/bundle/Resources/views/themes/admin/ui/mail/components'),
            ['strict_variables' => true]
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideForTestPlainNameIsPrintedAsText(): iterable
    {
        yield 'full name' => ['John Doe', 'John Doe'];
        yield 'markup is escaped' => ['<b>John</b>', '&lt;b&gt;John&lt;/b&gt;'];
        yield 'at sign without a domain' => ['john@localhost', 'john@localhost'];
        yield 'name that only contains an address' => ['John Doe john@example.com', 'John Doe john@example.com'];
    }

    #[DataProvider('provideForTestPlainNameIsPrintedAsText')]
    public function testPlainNameIsPrintedAsText(
        string $name,
        string $expectedOutput
    ): void {
        self::assertSame($expectedOutput, $this->twig->render(self::TEMPLATE, ['name' => $name]));
    }

    public function testEmailAddressBecomesAColouredMailtoLink(): void
    {
        $html = $this->twig->render(self::TEMPLATE, ['name' => 'john.doe@example.com']);

        self::assertMatchesRegularExpression(
            '~^<a[^>]*class="ibexa-email-link"[^>]*style="[^"]*color: #4e24a5;[^"]*"[^>]*href="mailto:john\.doe@example\.com"[^>]*>john\.doe@example\.com</a>$~',
            $html
        );
    }

    public function testHostileEmailAddressIsEscapedInTheLink(): void
    {
        $html = $this->twig->render(self::TEMPLATE, ['name' => '"><img/src=x>@example.com']);

        self::assertStringNotContainsString('<img', $html);
        self::assertStringContainsString('href="mailto:&quot;&gt;&lt;img/src=x&gt;@example.com"', $html);
        self::assertStringContainsString('>&quot;&gt;&lt;img/src=x&gt;@example.com</a>', $html);
    }
}
