<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Limitation;

/**
 * Interface for Limitation value mappers registry.
 */
interface LimitationValueMapperRegistryInterface
{
    /**
     * Returns all available mappers.
     *
     * @return LimitationValueMapperInterface[]
     */
    public function getMappers(): array;

    /**
     * Returns mapper corresponding to given Limitation Type.
     *
     * @throws \Ibexa\AdminUi\Exception\ValueMapperNotFoundException if no mapper exists for $limitationType
     */
    public function getMapper(string $limitationType): LimitationValueMapperInterface;

    public function hasMapper(string $limitationType): bool;

    public function addMapper(LimitationValueMapperInterface $mapper, string $limitationType): void;
}
