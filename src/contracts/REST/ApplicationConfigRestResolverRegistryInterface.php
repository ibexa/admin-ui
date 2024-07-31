<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\REST;

interface ApplicationConfigRestResolverRegistryInterface
{
    public function hasResolver(string $namespace, string $parameter): bool;

    public function hasResolvers(string $namespace): bool;

    public function getResolver(string $namespace, string $parameter): ?ApplicationConfigRestResolverInterface;

    /**
     * @return iterable<\Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestResolverInterface>
     */
    public function getResolvers(string $namespace): iterable;
}
