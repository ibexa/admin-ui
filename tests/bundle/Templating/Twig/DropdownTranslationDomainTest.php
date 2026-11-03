<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Templating\Twig;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Renders the dropdown component (component/dropdown/dropdown.html.twig) and checks the domain every visible
 * label is translated in: choices in `translation_domain`, the placeholder in `placeholder_translation_domain`,
 * falling back to `translation_domain` when it isn't passed.
 *
 * @coversNothing
 */
final class DropdownTranslationDomainTest extends TestCase
{
    private const CHOICE = 'status.active.label';
    private const PLACEHOLDER = 'status.placeholder';

    private Environment $twig;

    protected function setUp(): void
    {
        $loader = new FilesystemLoader();
        $loader->addPath(dirname(__DIR__, 4) . '/src/bundle/Resources/views/themes/admin', 'ibexadesign');

        // Appends the domain to every translated label, so the assertions show where each one was looked up.
        $translator = new class() implements TranslatorInterface {
            /**
             * @param array<string, mixed> $parameters
             */
            public function trans(
                string $id,
                array $parameters = [],
                ?string $domain = null,
                ?string $locale = null
            ): string {
                return $id === '' ? '' : $id . '@' . ($domain ?? 'messages');
            }

            public function getLocale(): string
            {
                return 'en';
            }
        };

        $this->twig = new Environment($loader, ['strict_variables' => true]);
        $this->twig->addExtension(new TranslationExtension($translator));
        $this->twig->addFilter(new TwigFilter('desc', static fn (string $message): string => $message));
        $this->twig->addFunction(new TwigFunction('ibexa_icon_path', static fn (string $icon): string => '#' . $icon));
        // Symfony's form test, only compiled here: with the default `custom_form: true` it is never called.
        $this->twig->addTest(new TwigTest('selectedchoice', static fn (): bool => false));
    }

    /**
     * @return iterable<string, array{array<string, mixed>, list<string>}>
     */
    public static function provideDomains(): iterable
    {
        yield 'placeholder in its own domain' => [
            ['translation_domain' => 'ibexa_choices', 'placeholder_translation_domain' => 'ibexa_test'],
            [self::CHOICE . '@ibexa_choices', self::PLACEHOLDER . '@ibexa_test'],
        ];
        yield 'placeholder not translated' => [
            ['translation_domain' => 'ibexa_choices', 'placeholder_translation_domain' => false],
            [self::CHOICE . '@ibexa_choices', self::PLACEHOLDER],
        ];
        yield 'no placeholder domain passed' => [
            ['translation_domain' => 'ibexa_test'],
            [self::CHOICE . '@ibexa_test', self::PLACEHOLDER . '@ibexa_test'],
        ];
        yield 'multiple, placeholder in its own domain' => [
            ['translation_domain' => 'ibexa_choices', 'placeholder_translation_domain' => 'ibexa_test', 'multiple' => true],
            [self::CHOICE . '@ibexa_choices', self::PLACEHOLDER . '@ibexa_test'],
        ];
    }

    /**
     * @dataProvider provideDomains
     *
     * @param array<string, mixed> $parameters
     * @param list<string> $expectedLabels
     */
    public function testChoicesAndPlaceholderAreTranslatedInTheirOwnDomain(
        array $parameters,
        array $expectedLabels
    ): void {
        $html = $this->twig->render('@ibexadesign/ui/component/dropdown/dropdown.html.twig', $parameters + [
            'choices' => [new ChoiceView('active', 'active', self::CHOICE)],
            'placeholder' => self::PLACEHOLDER,
        ]);

        self::assertSame($expectedLabels, $this->getVisibleLabels($html));
    }

    /**
     * Distinct texts of the selected item, the placeholders and the list items, sorted: a label translated in
     * the wrong domain, or translated twice, shows up as an extra entry.
     *
     * @return list<string>
     */
    private function getVisibleLabels(string $html): array
    {
        preg_match_all(
            '~<(span|li)\s+class="(?:ibexa-dropdown__item-label|ibexa-dropdown__selected-item-label|[^"]*ibexa-dropdown__selected-placeholder[^"]*)"[^>]*>(.*?)</\1>~s',
            $html,
            $matches
        );

        $labels = array_unique(array_filter(array_map(
            static fn (string $content): string => trim(strip_tags($content)),
            $matches[2]
        )));
        sort($labels);

        return $labels;
    }
}
