<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Siteaccess;

use Ibexa\Core\MVC\Symfony\SiteAccess;

/**
 * Resolves how a SiteAccess is presented to back office users.
 *
 * The default implementation knows only the SiteAccess name. Packages that provide SiteAccesses
 * (e.g. Site Factory) decorate it to contribute richer information.
 */
interface SiteAccessInfoResolverInterface
{
    public function resolve(SiteAccess $siteAccess): SiteAccessInfo;
}
