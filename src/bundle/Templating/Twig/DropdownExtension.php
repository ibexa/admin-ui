<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\AdminUi\UI\Dropdown\DropdownItemsMapper;
use InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @phpstan-import-type TDropdownItem from DropdownItemsMapper
 * @phpstan-import-type TDropdownGroup from DropdownItemsMapper
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

        $mapper = new DropdownItemsMapper($this->translator, $this->propertyAccessor, $translationDomain);

        return $mapper->map($preferredChoices, $choices);
    }
}
