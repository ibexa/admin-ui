<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\ContentTree;

use Ibexa\Rest\Value as RestValue;

/**
 * @phpstan-type TRestrictions array{
 *      hasAccess: bool,
 *      restrictedContentTypeIds?: array<int>,
 *      restrictedLanguageCodes?: array<string>,
 * }
 *
 * @phpstan-type TPermissionRestrictions array{
 *      create: TRestrictions,
 *      edit: TRestrictions,
 *      delete: TRestrictions,
 *      hide: TRestrictions,
 * }
 */
final class NodeExtendedInfo extends RestValue
{
    /** @phpstan-var TPermissionRestrictions|null */
    private ?array $permissions;

    /**
     * @phpstan-param TPermissionRestrictions|null $permissions
     */
    public function __construct(
        ?array $permissions = null
    ) {
        $this->permissions = $permissions;
    }

    /**
     * @return TPermissionRestrictions|null
     */
    public function getPermissionRestrictions(): ?array
    {
        return $this->permissions;
    }
}
