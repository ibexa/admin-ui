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

    private array $headers;

    public function __construct(string $tableIdentifier, array $headers)
    {
        $this->tableIdentifier = $tableIdentifier;
        $this->headers = $headers;
    }

    public function getTableIdentifier(): string
    {
        return $this->tableIdentifier;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function addHeader(array $column): void
    {
        $this->headers[] = $column;
    }

    public function addHeaderAt(int $index, array $header): void
    {
        array_splice($this->headers, $index, 0, [$header]);
    }
}
