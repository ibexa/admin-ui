<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Siteaccess;

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
     * @return array
     */
    public function getSiteaccessesForLocation(
        Location $location,
        int $versionNo = null,
        string $languageCode = null
    ): array;

    /**
     * Returns a complete list of Site Access objects.
     *
     * @return \Ibexa\Core\MVC\Symfony\SiteAccess[]
     */
    public function getSiteAccessesListForLocation(
        Location $location,
        ?int $versionNo = null,
        ?string $languageCode = null
    ): array;

    /**
     * Returns a complete list of Site Access names.
     *
     * @return array
     */
    public function getSiteaccesses(): array;
}

class_alias(SiteaccessResolverInterface::class, 'EzSystems\EzPlatformAdminUi\Siteaccess\SiteaccessResolverInterface');
