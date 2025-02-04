<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ValueResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @phpstan-template TValue of object
 */
abstract class AbstractValueResolver implements ValueResolverInterface
{
    /**
     * @return iterable<TValue>
     */
    final public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$this->supports($argument)) {
            return [];
        }

        $key = $this->getKey($request);
        if (!$this->validateKey($key)) {
            return [];
        }

        yield $this->load($key);
    }

    protected function supports(ArgumentMetadata $argument): bool
    {
        $argumentType = $argument->getType();
        if ($argumentType === null) {
            return false;
        }

        return is_a($argumentType, $this->getClass(), true);
    }

    /**
     * @phpstan-return array<string, mixed>
     */
    protected function getKey(Request $request): array
    {
        $key = [];
        foreach ($this->getRequestAttributes() as $name) {
            if (!$request->attributes->has($name)) {
                continue;
            }

            $key[$name] = $request->attributes->get($name);
        }

        return $key;
    }

    /**
     * @phpstan-param array<string, mixed> $key
     */
    protected function validateKey(array $key): bool
    {
        foreach ($this->getRequestAttributes() as $name) {
            if (!isset($key[$name])) {
                return false;
            }

            if (!is_string($key[$name])) {
                return false;
            }

            if (!$this->validateValue($key[$name])) {
                return false;
            }
        }

        return true;
    }

    protected function validateValue(string $value): bool
    {
        return true;
    }

    /**
     * @phpstan-return string[]
     */
    abstract protected function getRequestAttributes(): array;

    /**
     * @phpstan-return class-string<TValue>
     */
    abstract protected function getClass(): string;

    /**
     * @phpstan-param array<string, mixed> $key
     *
     * @phpstan-return TValue
     */
    abstract protected function load(array $key): object;
}
