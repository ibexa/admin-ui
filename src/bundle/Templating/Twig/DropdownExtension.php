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
 * @phpstan-type TChoice array<string, mixed>|object
 */
final class DropdownExtension extends AbstractExtension
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {}

    /**
     * @return TwigFunction[]
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
     * @param iterable<mixed> $choices
     * @param iterable<mixed> $preferredChoices
     *
     * @return array<int, TDropdownItem|TDropdownGroup>
     */
    public function getItems(
        iterable $choices,
        iterable $preferredChoices,
        string | false $translationDomain
    ): array {
        $seenIds = [];

        return [
            ...$this->mapChoices($preferredChoices, $translationDomain, $seenIds),
            ...$this->mapChoices($choices, $translationDomain, $seenIds),
        ];
    }

    /**
     * @param iterable<mixed> $choices
     * @param array<string, true> $seenIds
     *
     * @return array<int, TDropdownItem|TDropdownGroup>
     */
    private function mapChoices(
        iterable $choices,
        string | false $translationDomain,
        array &$seenIds
    ): array {
        $entries = [];

        foreach ($choices as $choice) {
            if (self::has($choice, 'choices')) {
                array_push($entries, ...$this->mapGroup($choice, $translationDomain, $seenIds));

                continue;
            }

            if (!self::has($choice, 'value') || !self::has($choice, 'label')) {
                throw new InvalidArgumentException(sprintf(
                    'Dropdown choices must have "value" and "label", or "choices" for a group, "%s" given.',
                    get_debug_type($choice)
                ));
            }

            $item = $this->mapChoice($choice, $translationDomain, $seenIds);

            if ($item !== null) {
                $entries[] = $item;
            }
        }

        return $entries;
    }

    /**
     * @param TChoice $group
     * @param array<string, true> $seenIds
     *
     * @return array<int, TDropdownItem|TDropdownGroup>
     */
    private function mapGroup(
        array | object $group,
        string | false $translationDomain,
        array &$seenIds
    ): array {
        $choices = self::read($group, 'choices');
        $label = self::has($group, 'label') ? self::read($group, 'label') : null;
        $items = $this->mapChoices(is_iterable($choices) ? $choices : [], $translationDomain, $seenIds);
        $label = $label === null ? '' : $this->translateLabel($label, [], $translationDomain);

        if ($items === [] || $label === '') {
            return $items;
        }

        return [[
            'label' => $label,
            'items' => $items,
        ]];
    }

    /**
     * @param TChoice $choice
     * @param array<string, true> $seenIds
     *
     * @return TDropdownItem|null
     */
    private function mapChoice(
        array | object $choice,
        string | false $translationDomain,
        array &$seenIds
    ): ?array {
        $id = (string) self::read($choice, 'value');

        if (isset($seenIds[$id])) {
            return null;
        }

        $seenIds[$id] = true;
        $parameters = $choice instanceof ChoiceView ? $choice->labelTranslationParameters : [];

        return [
            'id' => $id,
            'label' => $this->translateLabel(self::read($choice, 'label'), $parameters, $translationDomain),
        ];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function translateLabel(
        mixed $label,
        array $parameters,
        string | false $translationDomain
    ): string {
        if ($label instanceof TranslatableInterface) {
            return $label->trans($this->translator);
        }

        if ($label === false || $label === null) {
            return '';
        }

        if (!is_scalar($label)) {
            throw new InvalidArgumentException(sprintf(
                'Dropdown choice labels must be strings or translatable, "%s" given.',
                get_debug_type($label)
            ));
        }

        $label = (string) $label;

        if ($translationDomain === false) {
            return $label;
        }

        return $this->translator->trans($label, $parameters, $translationDomain);
    }

    private static function has(
        mixed $choice,
        string $key
    ): bool {
        if (is_array($choice)) {
            return array_key_exists($key, $choice);
        }

        return is_object($choice) && property_exists($choice, $key);
    }

    /**
     * @param TChoice $choice
     */
    private static function read(
        array | object $choice,
        string $key
    ): mixed {
        return is_array($choice) ? $choice[$key] : $choice->{$key};
    }
}
