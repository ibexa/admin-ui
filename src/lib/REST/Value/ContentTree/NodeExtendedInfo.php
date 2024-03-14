<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\ContentTree;

use Ibexa\Rest\Value as RestValue;

/**
 * @phpstan-type Restrictions array{
 *     hasAccess: bool,
 *     restrictedContentTypeIds: array<int>,
 *     restrictedLanguageCodes: array<string>,
 * }
 *
 * @phpstan-type PermissionRestrictions array{
 *     create: Restrictions,
 *     edit: Restrictions,
 * }
 */
class NodeExtendedInfo extends RestValue
{
    /** @phpstan-var PermissionRestrictions|null */
    private ?array $permissions;

    /**
     * @phpstan-param PermissionRestrictions|null $permissions
     */
    public function __construct(
        ?array $permissions = null,
    ) {
        $this->permissions = $permissions;
    }

    /**
     * @return PermissionRestrictions|null
     */
    public function getPermissionRestrictions(): ?array
    {
        return $this->permissions;
    }
}
