<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\REST;

interface ApplicationConfigRestGeneratorRegistryInterface
{
    public function hasGenerator(string $namespace, string $parameter): bool;

    public function hasGenerators(string $namespace): bool;

    public function getGenerator(string $namespace, string $parameter): ApplicationConfigRestGeneratorInterface;

    /**
     * @return iterable<\Ibexa\Contracts\AdminUi\REST\ApplicationConfigRestGeneratorInterface>
     */
    public function getGenerators(string $namespace): iterable;
}
