<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use InvalidArgumentException;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
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
        private readonly TranslatorInterface $translator,
        private readonly PropertyAccessorInterface $propertyAccessor
    ) {}

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ibexa_dropdown_items',
                $this->getItems(...),
                ['needs_context' => true]
            ),
        ];
    }

    /**
     * @param array<string, mixed> $context
     * @param iterable<mixed> $choices
     * @param iterable<mixed> $preferredChoices
     *
     * @return array<int, TDropdownItem|TDropdownGroup>
     */
    public function getItems(
        array $context,
        iterable $choices,
        iterable $preferredChoices
    ): array {
        $translationDomain = $context['translation_domain'] ?? null;

        if (!is_string($translationDomain) && $translationDomain !== false && $translationDomain !== null) {
            throw new InvalidArgumentException(sprintf(
                'The dropdown translation domain must be a string, false or null, "%s" given.',
                get_debug_type($translationDomain)
            ));
        }

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
        string | false | null $translationDomain,
        array &$seenIds
    ): array {
        $entries = [];

        foreach ($choices as $choice) {
            if ($this->has($choice, 'choices')) {
                array_push($entries, ...$this->mapGroup($choice, $translationDomain, $seenIds));

                continue;
            }

            if (!$this->has($choice, 'value') || !$this->has($choice, 'label')) {
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
        string | false | null $translationDomain,
        array &$seenIds
    ): array {
        $choices = $this->read($group, 'choices');

        if (!is_iterable($choices)) {
            throw new InvalidArgumentException(sprintf(
                'Dropdown group choices must be iterable, "%s" given.',
                get_debug_type($choices)
            ));
        }

        $items = $this->mapChoices($choices, $translationDomain, $seenIds);
        $label = $this->has($group, 'label')
            ? $this->translateLabel($this->read($group, 'label'), [], $translationDomain)
            : '';

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
        string | false | null $translationDomain,
        array &$seenIds
    ): ?array {
        $id = (string) $this->read($choice, 'value');

        if (isset($seenIds[$id])) {
            return null;
        }

        $seenIds[$id] = true;
        $parameters = $choice instanceof ChoiceView ? $choice->labelTranslationParameters : [];

        return [
            'id' => $id,
            'label' => $this->translateLabel($this->read($choice, 'label'), $parameters, $translationDomain),
        ];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function translateLabel(
        mixed $label,
        array $parameters,
        string | false | null $translationDomain
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
