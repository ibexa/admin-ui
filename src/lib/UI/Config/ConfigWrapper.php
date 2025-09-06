<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config;

use ArrayAccess;
use JsonSerializable;
use RuntimeException;

class ConfigWrapper implements ArrayAccess, JsonSerializable
{
    /**
     * @param array<mixed> $config
     */
    public function __construct(private array $config)
    {
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return isset($this->config[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->config[$offset];
    }

    #[\ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new RuntimeException('Configuration is readonly');
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset(mixed $offset): void
    {
        throw new RuntimeException('Configuration is readonly');
    }

    /**
     * @return array<mixed>
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return $this->config;
    }
}
