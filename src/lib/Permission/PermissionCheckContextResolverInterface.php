<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Permission;

use Ibexa\Contracts\AdminUi\Values\PermissionCheckContext;
use Symfony\Component\HttpFoundation\Request;

interface PermissionCheckContextResolverInterface
{
    public function resolve(string $module, string $function, Request $request): PermissionCheckContext;
}
