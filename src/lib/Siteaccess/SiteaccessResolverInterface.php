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
     * Accepts $location and returns all siteaccesses in which Content item can be previewed.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     * @param int|null $versionNo
     * @param string|null $languageCode
     *
     * @return string[]
     *
     * @deprecated Deprecated since Ibexa DXP 4.5.0.
     * Use { @see \Ibexa\AdminUi\Siteaccess\SiteaccessResolverInterface::getSiteAccessesList } instead.
     */
    public function getSiteaccessesForLocation(
        Location $location,
        int $versionNo = null,
        string $languageCode = null
    ): array;

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

    /**
     * @deprecated use \Ibexa\AdminUi\Siteaccess\SiteaccessResolverInterface::getSiteAccessesList instead.
     * Returns a complete list of Site Access names.
     *
     * @return string[]
     */
    public function getSiteaccesses(): array;
}
