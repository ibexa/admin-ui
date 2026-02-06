<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig\Components;

use Ibexa\Bundle\AdminUi\Templating\Twig\Components\Table\Column;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsTwigComponent(
    name: 'ibexa.Table',
    template: '@ibexadesign/components/table.html.twig',
)]
final class Table
{
    /**
     * @var iterable<object>
     */
    public iterable $data = [];

    public string $type = 'default';

    /** @var class-string|null */
    private ?string $dataType = null;

    public TranslatableMessage $emptyStateTitle;

    public ?TranslatableMessage $emptyStateDescription = null;

    public ?string $emptyStateExtraActions = null;

    /** @var array<string, mixed> */
    public array $parameters = [];

    /**
     * @var array<string, Column>
     */
    private array $columns = [];

    public function __construct()
    {
        $this->emptyStateTitle = new TranslatableMessage('search.no_results.title', [], 'ibexa_admin_ui');
    }

    /**
     * @param iterable<object> $data
     */
    public function mount(iterable $data = []): void
    {
        if ($data !== []) {
            $this->data = $data;
        }
    }

    public function getDataType(): ?string
    {
        return $this->dataType ??= $this->inferDataType();
    }

    /**
     * @return array<string, Column>
     */
    #[ExposeInTemplate('columns')]
    public function getColumns(): array
    {
        uasort($this->columns, static fn (Column $a, Column $b): int => $b->priority <=> $a->priority);

        return $this->columns;
    }

    /**
     * @param callable(mixed): string $renderer
     */
    public function addColumn(string $identifier, string $label, callable $renderer, int $priority = 0): self
    {
        $this->columns[$identifier] = new Column($identifier, $label, $renderer, $priority);

        return $this;
    }

    public function removeColumn(string $identifier): self
    {
        unset($this->columns[$identifier]);

        return $this;
    }

    /**
     * @return class-string|null
     */
    private function inferDataType(): ?string
    {
        $firstItem = null;
        foreach ($this->data as $item) {
            $firstItem = $item;
            break;
        }

        if (!is_object($firstItem)) {
            return null;
        }

        $candidates = array_merge(
            [get_class($firstItem)],
            class_parents($firstItem),
            class_implements($firstItem)
        );

        foreach ($this->data as $item) {
            $candidates = array_filter($candidates, static fn ($candidate): bool => $item instanceof $candidate);
            if (empty($candidates)) {
                return null;
            }
        }

        /** @var class-string|null $inferredType */
        $inferredType = reset($candidates);

        return $inferredType;
    }

    public function renderCell(Column $column, mixed $item): string
    {
        return (string) ($column->renderer)($item);
    }
}
