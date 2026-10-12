<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Siteaccess;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\MVC\Symfony\SiteAccess;

interface SiteaccessResolverInterface
{
    /**
     * @return SiteAccess[]
     */
    public function getSiteAccessesListForLocation(
        Location $location,
        ?int $versionNo = null,
        ?string $languageCode = null
    ): array;

    /**
     * @return SiteAccess[]
     */
    public function getSiteAccessesListForContent(Content $content): array;

    /**
     * @return SiteAccess[]
     */
    public function getSiteAccessesList(): array;
}
