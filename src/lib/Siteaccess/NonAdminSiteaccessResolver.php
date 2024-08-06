<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Siteaccess;

use Ibexa\AdminUi\Specification\SiteAccess\IsAdmin;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\MVC\Symfony\SiteAccess;

/**
 * Decorator for SiteaccessResolverInterface filtering out all non admin siteaccesses.
 */
class NonAdminSiteaccessResolver implements SiteaccessResolverInterface
{
    /** @var \Ibexa\AdminUi\Siteaccess\SiteaccessResolver */
    private $siteaccessResolver;

    /** @var string[] */
    private $siteAccessGroups;

    /**
     * @param \Ibexa\AdminUi\Siteaccess\SiteaccessResolver $siteaccessResolver
     * @param string[] $siteAccessGroups
     */
    public function __construct(SiteaccessResolver $siteaccessResolver, array $siteAccessGroups)
    {
        $this->siteaccessResolver = $siteaccessResolver;
        $this->siteAccessGroups = $siteAccessGroups;
    }

    /**
     * @return \Ibexa\Core\MVC\Symfony\SiteAccess[]
     */
    public function getSiteAccessesListForLocation(
        Location $location,
        ?int $versionNo = null,
        ?string $languageCode = null
    ): array {
        return array_filter(
            $this->siteaccessResolver->getSiteAccessesListForLocation($location, $versionNo, $languageCode),
            fn (SiteAccess $siteAccess): bool => !$this->isAdminSiteAccess($siteAccess)
        );
    }

    public function getSiteAccessesListForContent(Content $content): array
    {
        return array_filter(
            $this->siteaccessResolver->getSiteAccessesListForContent($content),
            fn (SiteAccess $siteAccess): bool => !$this->isAdminSiteAccess($siteAccess)
        );
    }

    public function getSiteAccessesList(): array
    {
        return array_filter(
            $this->siteaccessResolver->getSiteAccessesList(),
            fn (SiteAccess $siteAccess): bool => !$this->isAdminSiteAccess($siteAccess)
        );
    }

    private function isAdminSiteAccess(SiteAccess $siteAccess): bool
    {
        return (new IsAdmin($this->siteAccessGroups))->isSatisfiedBy($siteAccess);
    }
}
