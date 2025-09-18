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

    /** @var array<int, array<string, string|bool>> */
    private array $columns;

    /** @var mixed */
    private $row;

    /**
     * @param array<int, array<string, string|bool>> $columns
     * @param mixed $row
     */
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

    /**
     * @return mixed
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @return array<int, array<string, string|bool>>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param array<string, string|bool> $column
     */
    public function addColumn(array $column): void
    {
        $this->columns[] = $column;
    }

    /**
     * @param array<string, string|bool> $column
     */
    public function addColumnAt(int $index, array $column): void
    {
        array_splice($this->columns, $index, 0, [$column]);
    }
}
