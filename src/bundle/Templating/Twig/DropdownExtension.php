<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use InvalidArgumentException;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @phpstan-type TDropdownItem array{id: string, label: string}
 * @phpstan-type TDropdownGroup array{label: string, items: array<int, array<string, mixed>>}
 * @phpstan-type TGroupView object{label: string|null, choices: iterable<object>}
 */
final class DropdownExtension extends AbstractExtension
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ibexa_dropdown_items',
                $this->getItems(...)
            ),
        ];
    }

    /**
     * @param iterable<object> $choices
     * @param iterable<object> $preferredChoices
     *
     * @return array<int, TDropdownItem|TDropdownGroup>
     */
    public function getItems(iterable $choices, iterable $preferredChoices, string|false $translationDomain): array
    {
        $seenIds = [];

        return [
            ...$this->mapChoiceViews($preferredChoices, $translationDomain, $seenIds),
            ...$this->mapChoiceViews($choices, $translationDomain, $seenIds),
        ];
    }

    /**
     * @param iterable<object> $choiceViews
     * @param array<string, true> $seenIds
     *
     * @return array<int, TDropdownItem|TDropdownGroup>
     */
    private function mapChoiceViews(iterable $choiceViews, string|false $translationDomain, array &$seenIds): array
    {
        $entries = [];

        foreach ($choiceViews as $choiceView) {
            if ($choiceView instanceof ChoiceView) {
                $item = $this->mapChoiceView($choiceView, $translationDomain, $seenIds);

                if ($item !== null) {
                    $entries[] = $item;
                }

                continue;
            }

            if (!self::isGroupView($choiceView)) {
                throw new InvalidArgumentException(sprintf(
                    'Dropdown choices must be %s instances or group views with "label" and "choices", "%s" given.',
                    ChoiceView::class,
                    get_debug_type($choiceView)
                ));
            }

            array_push($entries, ...$this->mapGroupView($choiceView, $translationDomain, $seenIds));
        }

        return $entries;
    }

    /**
     * @phpstan-assert-if-true TGroupView $choiceView
     */
    private static function isGroupView(object $choiceView): bool
    {
        return property_exists($choiceView, 'label') && property_exists($choiceView, 'choices');
    }

    /**
     * A group without a label has no header to show, so its items join the surrounding level.
     *
     * @param TGroupView $groupView
     * @param array<string, true> $seenIds
     *
     * @return array<int, TDropdownItem|TDropdownGroup>
     */
    private function mapGroupView(object $groupView, string|false $translationDomain, array &$seenIds): array
    {
        $items = $this->mapChoiceViews($groupView->choices, $translationDomain, $seenIds);
        $label = $groupView->label === null ? '' : $this->translateLabel($groupView->label, [], $translationDomain);

        if ($items === [] || $label === '') {
            return $items;
        }

        return [[
            'label' => $label,
            'items' => $items,
        ]];
    }

    /**
     * @param array<string, true> $seenIds
     *
     * @return TDropdownItem|null
     */
    private function mapChoiceView(ChoiceView $choiceView, string|false $translationDomain, array &$seenIds): ?array
    {
        if (isset($seenIds[$choiceView->value])) {
            return null;
        }

        $seenIds[$choiceView->value] = true;

        return [
            'id' => $choiceView->value,
            'label' => $this->translateLabel($choiceView->label, $choiceView->labelTranslationParameters, $translationDomain),
        ];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function translateLabel(
        string|TranslatableInterface|false $label,
        array $parameters,
        string|false $translationDomain
    ): string {
        if ($label instanceof TranslatableInterface) {
            return $label->trans($this->translator);
        }

        if ($label === false || $translationDomain === false) {
            return (string) $label;
        }

        return $this->translator->trans($label, $parameters, $translationDomain);
    }
}
