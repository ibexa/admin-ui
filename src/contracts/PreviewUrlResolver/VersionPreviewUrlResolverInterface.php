<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\PreviewUrlResolver;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\MVC\Symfony\SiteAccess;

interface VersionPreviewUrlResolverInterface
{
    public function resolveUrl(
        VersionInfo $versionInfo,
        Location $location,
        Language $language,
        SiteAccess $siteAccess
    ): string;
}
