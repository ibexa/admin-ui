<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Siteaccess;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

interface SiteaccessResolverInterface
{
    /**
     * @return \Ibexa\Core\MVC\Symfony\SiteAccess[]
     */
    public function getSiteAccessesListForLocation(
        Location $location,
        ?int $versionNo = null,
        ?string $languageCode = null
    ): array;

    /**
     * @return \Ibexa\Core\MVC\Symfony\SiteAccess[]
     */
    public function getSiteAccessesListForContent(Content $content): array;

    /**
     * @return \Ibexa\Core\MVC\Symfony\SiteAccess[]
     */
    public function getSiteAccessesList(): array;
}
