<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

readonly class ScopeParameterBasedValue implements ProviderInterface
{
    public function __construct(
        protected ConfigResolverInterface $configResolver,
        protected string $parameterName,
        protected ?string $namespace = null,
        protected ?string $scope = null
    ) {
    }

    public function getConfig(): mixed
    {
        return $this->configResolver->getParameter(
            $this->parameterName,
            $this->namespace,
            $this->scope
        );
    }
}
