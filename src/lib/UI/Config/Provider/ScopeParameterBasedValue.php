<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

class ScopeParameterBasedValue implements ProviderInterface
{
    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    protected $configResolver;

    /** @var string */
    protected $parameterName;

    /** @var string|null */
    protected $namespace;

    /** @var string|null */
    protected $scope;

    public function __construct(
        ConfigResolverInterface $configResolver,
        string $parameterName,
        ?string $namespace = null,
        ?string $scope = null
    ) {
        $this->configResolver = $configResolver;
        $this->parameterName = $parameterName;
        $this->namespace = $namespace;
        $this->scope = $scope;
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function getConfig()
    {
        return $this->configResolver->getParameter($this->parameterName, $this->namespace, $this->scope);
    }
}
