<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\REST;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

interface ApplicationConfigRestGeneratorInterface
{
    public function supportsNamespace(string $namespace): bool;

    public function supportsParameter(string $parameterName): bool;

    /**
     * @param mixed $parameter
     */
    public function generate($parameter, Generator $generator, Visitor $visitor): void;
}
