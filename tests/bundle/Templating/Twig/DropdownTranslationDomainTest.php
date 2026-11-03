<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Templating\Twig;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Forms;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Renders the back-office dropdown (form_fields/dropdown_widget.html.twig, component/dropdown/dropdown.html.twig)
 * and checks the domain every visible label is translated in: choices in `choice_translation_domain`, the
 * placeholder in the field's `translation_domain`. A field without a `translation_domain` keeps translating its
 * placeholder in the choice domain, as before.
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
        $rootDir = dirname(__DIR__, 4);

        $loader = new FilesystemLoader([$rootDir . '/vendor/symfony/twig-bridge/Resources/views/Form']);
        $loader->addPath($rootDir . '/src/bundle/Resources/views/themes/admin', 'ibexadesign');

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
        $this->twig->addExtension(new FormExtension());
        $this->twig->addFilter(new TwigFilter('desc', static fn (string $message): string => $message));
        $this->twig->addFunction(new TwigFunction('ibexa_icon_path', static fn (string $icon): string => '#' . $icon));

        $engine = new TwigRendererEngine(
            ['form_div_layout.html.twig', '@ibexadesign/ui/form_fields/dropdown_widget.html.twig'],
            $this->twig
        );
        $this->twig->addRuntimeLoader(new FactoryRuntimeLoader([
            FormRenderer::class => static fn (): FormRenderer => new FormRenderer($engine),
        ]));
    }

    /**
     * @return iterable<string, array{array<string, mixed>, list<string>}>
     */
    public static function provideFieldOptions(): iterable
    {
        yield 'choices not translated' => [
            ['translation_domain' => 'ibexa_test', 'choice_translation_domain' => false, 'placeholder' => self::PLACEHOLDER],
            [self::CHOICE, self::PLACEHOLDER . '@ibexa_test'],
        ];
        yield 'choices in another domain' => [
            ['translation_domain' => 'ibexa_test', 'choice_translation_domain' => 'ibexa_choices', 'placeholder' => self::PLACEHOLDER],
            [self::CHOICE . '@ibexa_choices', self::PLACEHOLDER . '@ibexa_test'],
        ];
        yield 'multiple, choices in another domain' => [
            [
                'translation_domain' => 'ibexa_test',
                'choice_translation_domain' => 'ibexa_choices',
                'multiple' => true,
                'attr' => ['placeholder' => self::PLACEHOLDER],
            ],
            [self::CHOICE . '@ibexa_choices', self::PLACEHOLDER . '@ibexa_test'],
        ];
        yield 'only choice_translation_domain set' => [
            ['choice_translation_domain' => 'ibexa_choices', 'placeholder' => self::PLACEHOLDER],
            [self::CHOICE . '@ibexa_choices', self::PLACEHOLDER . '@ibexa_choices'],
        ];
    }

    /**
     * @dataProvider provideFieldOptions
     *
     * @param array<string, mixed> $fieldOptions
     * @param list<string> $expectedLabels
     */
    public function testFormWidgetTranslatesChoicesAndPlaceholderInTheirOwnDomain(
        array $fieldOptions,
        array $expectedLabels
    ): void {
        $form = Forms::createFormFactory()
            ->createNamedBuilder('filter', FormType::class)
            ->add('status', ChoiceType::class, $fieldOptions + ['choices' => [self::CHOICE => 'active']])
            ->getForm();

        $html = $this->twig
            ->createTemplate('{{ form_widget(form.status) }}')
            ->render(['form' => $form->createView()]);

        self::assertSame($expectedLabels, $this->getVisibleLabels($html));
    }

    public function testComponentIncludedWithoutPlaceholderDomainTranslatesEverythingInTranslationDomain(): void
    {
        $html = $this->twig
            ->createTemplate("{% include '@ibexadesign/ui/component/dropdown/dropdown.html.twig' with {
                source: '', choices: choices, value: null, custom_form: true,
                placeholder: 'status.placeholder', translation_domain: 'ibexa_test'
            } %}")
            ->render(['choices' => [new ChoiceView('active', 'active', self::CHOICE)]]);

        self::assertSame(
            [self::CHOICE . '@ibexa_test', self::PLACEHOLDER . '@ibexa_test'],
            $this->getVisibleLabels($html)
        );
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
