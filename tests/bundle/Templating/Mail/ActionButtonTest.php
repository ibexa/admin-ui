<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Templating\Mail;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class ActionButtonTest extends TestCase
{
    private const string TEMPLATE = '@ibexadesign/ui/mail/components/action_btn.html.twig';

    private Environment $twig;

    protected function setUp(): void
    {
        $themeLoader = new FilesystemLoader();
        $themeLoader->addPath(dirname(__DIR__, 4) . '/src/bundle/Resources/views/themes/admin', 'ibexadesign');

        $this->twig = new Environment($themeLoader, ['strict_variables' => true]);
    }

    public function testDefaultsToTheBrandColor(): void
    {
        $html = $this->twig->render(self::TEMPLATE, ['url' => 'https://example.com', 'label' => 'Open']);

        self::assertStringContainsString('<td bgcolor="#4e24a5" style="background-color: #4e24a5;', $html);
        self::assertMatchesRegularExpression('~<a style="[^"]*background-color: #4e24a5;[^"]*" href="https://example.com">Open</a>~', $html);
    }

    public function testBackgroundColorAppliesToTheCellAndTheLink(): void
    {
        $html = $this->twig->render(self::TEMPLATE, [
            'url' => 'https://example.com',
            'label' => 'Open',
            'bg_color' => '#cc0000',
        ]);

        self::assertStringContainsString('<td bgcolor="#cc0000" style="background-color: #cc0000;', $html);
        self::assertMatchesRegularExpression('~<a style="[^"]*background-color: #cc0000;[^"]*" href=~', $html);
        self::assertStringNotContainsString('#4e24a5', $html);
    }
}
