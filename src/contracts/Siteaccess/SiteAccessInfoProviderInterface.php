<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Siteaccess;

use Ibexa\Core\MVC\Symfony\SiteAccess;

/**
 * Provides display information for the SiteAccesses a package owns.
 *
 * Implementations are tagged with {@see self::SERVICE_TAG}; the first provider (by tag priority)
 * that supports a SiteAccess resolves it. When none does, the SiteAccess name is used.
 */
interface SiteAccessInfoProviderInterface
{
    public const string SERVICE_TAG = 'ibexa.admin_ui.site_access.info_provider';

    public function supports(SiteAccess $siteAccess): bool;

    public function resolve(SiteAccess $siteAccess): SiteAccessInfo;
}
