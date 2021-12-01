<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Siteaccess;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Ibexa\AdminUi\Specification\SiteAccess\IsAdmin;

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

    public function getSiteaccessesForLocation(
        Location $location,
        int $versionNo = null,
        string $languageCode = null
    ): array {
        return $this->filter(
            $this->siteaccessResolver->getSiteaccessesForLocation($location, $versionNo, $languageCode)
        );
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
            function ($siteAccess) {
                return !$this->isAdminSiteAccess($siteAccess);
            }
        );
    }

    public function getSiteaccesses(): array
    {
        return $this->filter($this->siteaccessResolver->getSiteaccesses());
    }

    private function filter(array $siteaccesses): array
    {
        return array_diff($siteaccesses, $this->siteAccessGroups['admin_group']);
    }

    private function isAdminSiteAccess(SiteAccess $siteAccess): bool
    {
        return (new IsAdmin($this->siteAccessGroups))->isSatisfiedBy($siteAccess);
    }
}

class_alias(NonAdminSiteaccessResolver::class, 'EzSystems\EzPlatformAdminUi\Siteaccess\NonAdminSiteaccessResolver');
