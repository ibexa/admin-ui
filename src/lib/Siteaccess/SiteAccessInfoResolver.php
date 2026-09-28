<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Siteaccess;

use Ibexa\Contracts\AdminUi\Siteaccess\SiteAccessInfo;
use Ibexa\Contracts\AdminUi\Siteaccess\SiteAccessInfoResolverInterface;
use Ibexa\Core\MVC\Symfony\SiteAccess;

final class SiteAccessInfoResolver implements SiteAccessInfoResolverInterface
{
    public function resolve(SiteAccess $siteAccess): SiteAccessInfo
    {
        return new SiteAccessInfo($siteAccess->name);
    }
}
