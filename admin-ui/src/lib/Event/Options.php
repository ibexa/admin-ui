<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Event;

use Ibexa\Contracts\Core\Options\MutableOptionsBag;
use Ibexa\Contracts\Core\Repository\Exceptions\OutOfBoundsException;

final class Options implements MutableOptionsBag
{
    /** @var array<string, mixed> */
    private array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function all(): array
    {
        return $this->options;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->options[$key]);
    }

    public function set(string $key, mixed $value): void
    {
        $this->options[$key] = $value;
    }

    public function remove(string $key): void
    {
        if (!array_key_exists($key, $this->options)) {
            throw new OutOfBoundsException(
                sprintf("Option '%s' doesn't exist", $key)
            );
        }

        unset($this->options[$key]);
    }
}
