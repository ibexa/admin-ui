<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Permission;

use Ibexa\AdminUi\Values\PermissionCheckContext;
use Ibexa\Contracts\Core\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

final class PermissionCheckContextResolver implements PermissionCheckContextResolverInterface
{
    /** @var iterable<\Ibexa\Contracts\AdminUi\Permission\PermissionCheckContextProviderInterface> */
    private iterable $permissionContextProviders;

    /**
     * @param iterable<\Ibexa\Contracts\AdminUi\Permission\PermissionCheckContextProviderInterface> $permissionContextProviders
     */
    public function __construct(iterable $permissionContextProviders)
    {
        $this->permissionContextProviders = $permissionContextProviders;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Exception\InvalidArgumentException
     */
    public function resolve(string $module, string $function, Request $request): PermissionCheckContext
    {
        foreach ($this->permissionContextProviders as $provider) {
            if ($provider->supports($module, $function)) {
                return $provider->getPermissionCheckContext($module, $function, $request);
            }
        }

        throw new InvalidArgumentException(
            '$request',
            'Unsupported permission context.'
        );
    }
}
