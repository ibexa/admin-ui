<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Event;

class CollectCustomTableColumnsEvent
{
    private string $tableIdentifier;

    private array $columns;

    private $row;

    public function __construct(string $tableIdentifier, array $columns, $row)
    {
        $this->tableIdentifier = $tableIdentifier;
        $this->columns = $columns;
        $this->row = $row;
    }

    public function getTableIdentifier(): string
    {
        return $this->tableIdentifier;
    }

    public function getRow()
    {
        return $this->row;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function addColumn(array $column): void
    {
        $this->columns[] = $column;
    }

    public function addColumnAt(int $index, array $column): void
    {
        array_splice($this->columns, $index, 0, [$column]);
    }
}
