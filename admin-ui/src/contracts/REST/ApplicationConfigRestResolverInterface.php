<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\REST;

interface ApplicationConfigRestResolverInterface
{
    public function supportsNamespace(string $namespace): bool;

    public function supportsParameter(string $parameterName): bool;

    /**
     * @param array<mixed> $config
     *
     * @return mixed
     */
    public function resolve(array $config);
}
