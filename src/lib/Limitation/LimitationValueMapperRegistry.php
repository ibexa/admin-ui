<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Limitation;

use Ibexa\AdminUi\Exception\ValueMapperNotFoundException;

/**
 * Registry for Limitation value mappers.
 */
final class LimitationValueMapperRegistry implements LimitationValueMapperRegistryInterface
{
    /**
     * @param array<string, \Ibexa\AdminUi\Limitation\LimitationValueMapperInterface> $limitationValueMappers
     */
    public function __construct(private array $limitationValueMappers = [])
    {
    }

    /**
     * @return array<string, \Ibexa\AdminUi\Limitation\LimitationValueMapperInterface>
     */
    public function getMappers(): array
    {
        return $this->limitationValueMappers;
    }

    public function getMapper(string $limitationType): LimitationValueMapperInterface
    {
        if (!$this->hasMapper($limitationType)) {
            throw new ValueMapperNotFoundException($limitationType);
        }

        return $this->limitationValueMappers[$limitationType];
    }

    public function hasMapper(string $limitationType): bool
    {
        return isset($this->limitationValueMappers[$limitationType]);
    }

    public function addMapper(LimitationValueMapperInterface $mapper, string $limitationType): void
    {
        $this->limitationValueMappers[$limitationType] = $mapper;
    }
}
