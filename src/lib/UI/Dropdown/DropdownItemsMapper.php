<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dropdown;

use InvalidArgumentException;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @phpstan-type TDropdownItem array{id: string, label: string}
 * @phpstan-type TDropdownGroup array{label: string, items: array<int, array<string, mixed>>}
 * @phpstan-type TChoice array<string, mixed>|object
 *
 * @internal
 */
final class DropdownItemsMapper
{
    /** @var array<string, true> */
    private array $seenIds = [];

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly string | false | null $translationDomain
    ) {}

    /**
     * @param iterable<mixed> $preferredChoices
     * @param iterable<mixed> $choices
     *
     * @return array<int, TDropdownItem|TDropdownGroup>
     */
    public function map(
        iterable $preferredChoices,
        iterable $choices
    ): array {
        $this->seenIds = [];

        return [
            ...$this->mapChoices($preferredChoices),
            ...$this->mapChoices($choices),
        ];
    }

    /**
     * @param iterable<mixed> $choices
     *
     * @return array<int, TDropdownItem|TDropdownGroup>
     */
    private function mapChoices(iterable $choices): array
    {
        $entries = [];

        foreach ($choices as $choice) {
            if ($this->has($choice, 'choices')) {
                array_push($entries, ...$this->mapGroup($choice));

                continue;
            }

            if (!$this->has($choice, 'value') || !$this->has($choice, 'label')) {
                throw new InvalidArgumentException(sprintf(
                    'Dropdown choices must have "value" and "label", or "choices" for a group, "%s" given.',
                    get_debug_type($choice)
                ));
            }

            $item = $this->mapChoice($choice);

            if ($item !== null) {
                $entries[] = $item;
            }
        }

        return $entries;
    }

    /**
     * @param TChoice $group
     *
     * @return array<int, TDropdownItem|TDropdownGroup>
     */
    private function mapGroup(array | object $group): array
    {
        $choices = $this->read($group, 'choices');

        if (!is_iterable($choices)) {
            throw new InvalidArgumentException(sprintf(
                'Dropdown group choices must be iterable, "%s" given.',
                get_debug_type($choices)
            ));
        }

        $items = $this->mapChoices($choices);
        $label = $this->has($group, 'label') ? $this->translateLabel($this->read($group, 'label'), []) : '';

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
     *
     * @return TDropdownItem|null
     */
    private function mapChoice(array | object $choice): ?array
    {
        $id = (string) $this->read($choice, 'value');

        if (isset($this->seenIds[$id])) {
            return null;
        }

        $this->seenIds[$id] = true;
        $parameters = $choice instanceof ChoiceView ? $choice->labelTranslationParameters : [];

        return [
            'id' => $id,
            'label' => $this->translateLabel($this->read($choice, 'label'), $parameters),
        ];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function translateLabel(
        mixed $label,
        array $parameters
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

        $text = (string) $label;

        if ($this->translationDomain === false) {
            return $text;
        }

        return $this->translator->trans($text, $parameters, $this->translationDomain);
    }

    private function has(
        mixed $choice,
        string $key
    ): bool {
        if (is_array($choice)) {
            return array_key_exists($key, $choice);
        }

        return is_object($choice) && $this->propertyAccessor->isReadable($choice, $key);
    }

    /**
     * @param TChoice $choice
     */
    private function read(
        array | object $choice,
        string $key
    ): mixed {
        return is_array($choice) ? $choice[$key] : $this->propertyAccessor->getValue($choice, $key);
    }
}
