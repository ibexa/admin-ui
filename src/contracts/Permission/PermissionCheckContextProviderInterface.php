<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Permission;

use Ibexa\AdminUi\Values\PermissionCheckContext;
use Symfony\Component\HttpFoundation\Request;

interface PermissionCheckContextProviderInterface
{
    public function supports(string $module, string $function): bool;

    public function getPermissionCheckContext(string $module, string $function, Request $request): PermissionCheckContext;
}
