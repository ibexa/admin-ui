<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config;

use ArrayAccess;
use JsonSerializable;
use ReturnTypeWillChange;
use RuntimeException;

class ConfigWrapper implements ArrayAccess, JsonSerializable
{
    private array $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    #[ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->config[$offset];
    }

    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        throw new RuntimeException('Configuration is readonly');
    }

    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        throw new RuntimeException('Configuration is readonly');
    }

    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->config;
    }
}
