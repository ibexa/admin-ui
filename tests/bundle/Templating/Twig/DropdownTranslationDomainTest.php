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
 * Renders the back-office dropdown form widget (form_fields/dropdown_widget.html.twig) and the dropdown
 * component (component/dropdown/dropdown.html.twig), and checks in which translation domain the choice
 * labels and the placeholder are translated. Like Symfony, choices use `choice_translation_domain` and the
 * placeholder uses the field's `translation_domain`.
 *
 * @coversNothing
 */
final class DropdownTranslationDomainTest extends TestCase
{
    private const THEME = '@ibexadesign/ui/form_fields/dropdown_widget.html.twig';
    private const CHOICE = 'status.active.label';
    private const PLACEHOLDER = 'status.placeholder';

    private Environment $twig;

    protected function setUp(): void
    {
        $rootDir = dirname(__DIR__, 4);

        $loader = new FilesystemLoader([$rootDir . '/vendor/symfony/twig-bridge/Resources/views/Form']);
        $loader->addPath($rootDir . '/src/bundle/Resources/views/themes/admin', 'ibexadesign');

        // Echoes the domain each label is translated in; a null domain is "messages", as in Symfony's Translator.
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

        $engine = new TwigRendererEngine(['form_div_layout.html.twig', self::THEME], $this->twig);
        $this->twig->addRuntimeLoader(new FactoryRuntimeLoader([
            FormRenderer::class => static fn (): FormRenderer => new FormRenderer($engine),
        ]));
    }

    /**
     * @return iterable<string, array{array<string, mixed>, array<string, mixed>, string}>
     */
    public static function provideChoiceDomains(): iterable
    {
        yield 'translation_domain on the field' => [[], ['translation_domain' => 'ibexa_test'], self::CHOICE . '@ibexa_test'];
        yield 'translation_domain on the parent form' => [['translation_domain' => 'ibexa_test'], [], self::CHOICE . '@ibexa_test'];
        yield 'choice_translation_domain on the field' => [[], ['choice_translation_domain' => 'ibexa_test'], self::CHOICE . '@ibexa_test'];
        yield 'choice_translation_domain false' => [['translation_domain' => 'ibexa_test'], ['choice_translation_domain' => false], self::CHOICE];
        yield 'no translation domain anywhere' => [[], [], self::CHOICE];
    }

    /**
     * @dataProvider provideChoiceDomains
     *
     * @param array<string, mixed> $formOptions
     * @param array<string, mixed> $fieldOptions
     */
    public function testChoiceLabelsUseTheChoiceTranslationDomain(
        array $formOptions,
        array $fieldOptions,
        string $expectedLabel
    ): void {
        $html = $this->renderChoiceWidget($formOptions, $fieldOptions);

        self::assertSame([$expectedLabel], $this->getLabels($html, 'ibexa-dropdown__item-label'));
        self::assertSame([$expectedLabel], $this->getLabels($html, 'ibexa-dropdown__selected-item-label'));
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string, string}>
     */
    public static function providePlaceholderDomains(): iterable
    {
        yield 'same domain for choices and placeholder' => [
            ['translation_domain' => 'ibexa_test'],
            self::CHOICE . '@ibexa_test',
            self::PLACEHOLDER . '@ibexa_test',
        ];
        yield 'choices not translated' => [
            ['translation_domain' => 'ibexa_test', 'choice_translation_domain' => false],
            self::CHOICE,
            self::PLACEHOLDER . '@ibexa_test',
        ];
        yield 'choices in another domain' => [
            ['translation_domain' => 'ibexa_test', 'choice_translation_domain' => 'ibexa_choices'],
            self::CHOICE . '@ibexa_choices',
            self::PLACEHOLDER . '@ibexa_test',
        ];
        yield 'only choice_translation_domain set' => [
            ['choice_translation_domain' => 'ibexa_choices'],
            self::CHOICE . '@ibexa_choices',
            self::PLACEHOLDER . '@ibexa_choices',
        ];
        yield 'placeholder not translated' => [
            ['translation_domain' => false, 'choice_translation_domain' => 'ibexa_choices'],
            self::CHOICE . '@ibexa_choices',
            self::PLACEHOLDER,
        ];
    }

    /**
     * @dataProvider providePlaceholderDomains
     *
     * @param array<string, mixed> $fieldOptions
     */
    public function testPlaceholderUsesTheFieldTranslationDomain(
        array $fieldOptions,
        string $expectedChoiceLabel,
        string $expectedPlaceholder
    ): void {
        $html = $this->renderChoiceWidget([], $fieldOptions + ['placeholder' => self::PLACEHOLDER]);

        self::assertSame(
            [$expectedPlaceholder, $expectedChoiceLabel],
            $this->getLabels($html, 'ibexa-dropdown__item-label')
        );
        self::assertSame([$expectedPlaceholder], $this->getLabels($html, 'ibexa-dropdown__selected-item-label'));
    }

    /**
     * @dataProvider providePlaceholderDomains
     *
     * @param array<string, mixed> $fieldOptions
     */
    public function testPlaceholderOfMultipleChoiceUsesTheFieldTranslationDomain(
        array $fieldOptions,
        string $expectedChoiceLabel,
        string $expectedPlaceholder
    ): void {
        $html = $this->renderChoiceWidget([], $fieldOptions + [
            'multiple' => true,
            'attr' => ['placeholder' => self::PLACEHOLDER],
        ]);

        self::assertSame([$expectedChoiceLabel], $this->getLabels($html, 'ibexa-dropdown__item-label'));
        self::assertSame([$expectedPlaceholder], $this->getLabels($html, 'ibexa-dropdown__selected-item-label'));
        // Both placeholder elements a multiple dropdown renders, one with a label span and one without.
        self::assertSame([$expectedPlaceholder, $expectedPlaceholder], $this->getSelectedPlaceholders($html));
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function provideComponentArguments(): iterable
    {
        yield 'translation_domain only' => [
            "translation_domain: 'ibexa_test'",
            self::CHOICE . '@ibexa_test',
            self::PLACEHOLDER . '@ibexa_test',
        ];
        yield 'separate placeholder_translation_domain' => [
            "translation_domain: 'ibexa_choices', placeholder_translation_domain: 'ibexa_test'",
            self::CHOICE . '@ibexa_choices',
            self::PLACEHOLDER . '@ibexa_test',
        ];
        yield 'translation_domain false' => ['translation_domain: false', self::CHOICE, self::PLACEHOLDER];
        yield 'no domain passed' => ['', self::CHOICE, self::PLACEHOLDER];
    }

    /**
     * @dataProvider provideComponentArguments
     */
    public function testComponentIncludedDirectly(
        string $translationDomainArguments,
        string $expectedChoiceLabel,
        string $expectedPlaceholder
    ): void {
        $template = $this->twig->createTemplate(sprintf(
            "{%% include '@ibexadesign/ui/component/dropdown/dropdown.html.twig' with {
                source: '', choices: choices, value: null, custom_form: true, placeholder: '%s'%s
            } %%}",
            self::PLACEHOLDER,
            $translationDomainArguments === '' ? '' : ', ' . $translationDomainArguments
        ));

        $html = $template->render(['choices' => [new ChoiceView('active', 'active', self::CHOICE)]]);

        self::assertSame(
            [$expectedPlaceholder, $expectedChoiceLabel],
            $this->getLabels($html, 'ibexa-dropdown__item-label')
        );
        self::assertSame([$expectedPlaceholder], $this->getLabels($html, 'ibexa-dropdown__selected-item-label'));
    }

    /**
     * @param array<string, mixed> $formOptions
     * @param array<string, mixed> $fieldOptions
     */
    private function renderChoiceWidget(
        array $formOptions,
        array $fieldOptions
    ): string {
        $form = Forms::createFormFactory()
            ->createNamedBuilder('filter', FormType::class, null, $formOptions)
            ->add('status', ChoiceType::class, $fieldOptions + [
                'choices' => [self::CHOICE => 'active'],
            ])
            ->getForm();

        return $this->twig
            ->createTemplate('{{ form_widget(form.status) }}')
            ->render(['form' => $form->createView()]);
    }

    /**
     * @return list<string>
     */
    private function getLabels(
        string $html,
        string $class
    ): array {
        preg_match_all('~<span class="' . $class . '">(.*?)</span>~s', $html, $matches);

        return array_values(array_filter(array_map('trim', $matches[1]), static fn (string $label): bool => $label !== ''));
    }

    /**
     * @return list<string>
     */
    private function getSelectedPlaceholders(string $html): array
    {
        preg_match_all('~<li\s+class="[^"]*ibexa-dropdown__selected-placeholder[^"]*"[^>]*>(.*?)</li>~s', $html, $matches);

        return array_map(static fn (string $content): string => trim(strip_tags($content)), $matches[1]);
    }
}
