<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Limitation;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use InvalidArgumentException;

final class LimitationFormMapperRegistry implements LimitationFormMapperRegistryInterface
{
    /** @var \Ibexa\AdminUi\Limitation\LimitationFormMapperInterface[] */
    private array $limitationFormMappers = [];

    public function getMappers(): array
    {
        return $this->limitationFormMappers;
    }

    public function addMapper(LimitationFormMapperInterface $mapper, string $limitationIdentifier): void
    {
        $this->limitationFormMappers[$limitationIdentifier] = $mapper;
    }

    /**
     * Returns mapper corresponding to given Limitation identifier.
     *
     * @throws \InvalidArgumentException if no mapper exists for $fieldTypeIdentifier
     */
    public function getMapper(string $limitationIdentifier): LimitationFormMapperInterface
    {
        if (!$this->hasMapper($limitationIdentifier)) {
            throw new InvalidArgumentException("No LimitationFormMapper found for '$limitationIdentifier'");
        }

        return $this->limitationFormMappers[$limitationIdentifier];
    }

    public function hasMapper(string $limitationIdentifier): bool
    {
        return isset($this->limitationFormMappers[$limitationIdentifier]);
    }
}
