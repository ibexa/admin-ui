<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Limitation;

/**
 * Interface for Limitation form mappers registry.
 */
interface LimitationFormMapperRegistryInterface
{
    /**
     * @return \Ibexa\AdminUi\Limitation\LimitationFormMapperInterface[]
     */
    public function getMappers(): array;

    /**
     * Returns mapper corresponding to given Limitation identifier.
     *
     * @throws \InvalidArgumentException if no mapper exists for $limitationIdentifier
     */
    public function getMapper(string $limitationIdentifier): LimitationFormMapperInterface;

    public function hasMapper(string $limitationIdentifier): bool;

    public function addMapper(LimitationFormMapperInterface $mapper, string $limitationIdentifier): void;
}
