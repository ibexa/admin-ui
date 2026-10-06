<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @phpstan-type TDropdownItem array{id: string, label: string}
 * @phpstan-type TDropdownGroup array{label: string, items: array<int, array<string, mixed>>}
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
     * @param iterable<\Symfony\Component\Form\ChoiceList\View\ChoiceView|\Symfony\Component\Form\ChoiceList\View\ChoiceGroupView> $choices
     * @param iterable<\Symfony\Component\Form\ChoiceList\View\ChoiceView|\Symfony\Component\Form\ChoiceList\View\ChoiceGroupView> $preferredChoices
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
     * @param iterable<\Symfony\Component\Form\ChoiceList\View\ChoiceView|\Symfony\Component\Form\ChoiceList\View\ChoiceGroupView> $choiceViews
     * @param array<string, true> $seenIds
     *
     * @return array<int, TDropdownItem|TDropdownGroup>
     */
    private function mapChoiceViews(iterable $choiceViews, string|false $translationDomain, array &$seenIds): array
    {
        $entries = [];

        foreach ($choiceViews as $choiceView) {
            $entry = $choiceView instanceof ChoiceGroupView
                ? $this->mapGroupView($choiceView, $translationDomain, $seenIds)
                : $this->mapChoiceView($choiceView, $translationDomain, $seenIds);

            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    /**
     * @param array<string, true> $seenIds
     *
     * @return TDropdownGroup|null
     */
    private function mapGroupView(ChoiceGroupView $groupView, string|false $translationDomain, array &$seenIds): ?array
    {
        $items = $this->mapChoiceViews($groupView->choices, $translationDomain, $seenIds);

        if ($items === []) {
            return null;
        }

        return [
            'label' => $this->translateLabel($groupView->label, [], $translationDomain),
            'items' => $items,
        ];
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
