<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CollectCustomTableHeadersEvent extends Event
{
    private string $tableIdentifier;

    /** @var array<int, array<string, string|bool>> */
    private array $headers;

    /**
     * @param array<int, array<string, string|bool>> $headers
     */
    public function __construct(string $tableIdentifier, array $headers)
    {
        $this->tableIdentifier = $tableIdentifier;
        $this->headers = $headers;
    }

    public function getTableIdentifier(): string
    {
        return $this->tableIdentifier;
    }

    /**
     * @return array<int, array<string, string|bool>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array<string, string|bool> $column
     */
    public function addHeader(array $column): void
    {
        $this->headers[] = $column;
    }

    /**
     * @param array<string, string|bool> $header
     */
    public function addHeaderAt(int $index, array $header): void
    {
        array_splice($this->headers, $index, 0, [$header]);
    }
}
