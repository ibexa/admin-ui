<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Siteaccess;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessService;

class SiteaccessResolver implements SiteaccessResolverInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /** @var \Ibexa\AdminUi\Siteaccess\SiteaccessPreviewVoterInterface[] */
    private $siteAccessPreviewVoters;

    /** @var \Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessService */
    private $siteAccessService;

    private LocationService $locationService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param iterable $siteaccessPreviewVoters
     * @param array $siteAccesses
     */
    public function __construct(
        ContentService $contentService,
        iterable $siteaccessPreviewVoters,
        SiteAccessService $siteAccessService,
        LocationService $locationService
    ) {
        $this->contentService = $contentService;
        $this->siteAccessPreviewVoters = $siteaccessPreviewVoters;
        $this->siteAccessService = $siteAccessService;
        $this->locationService = $locationService;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     * @param int|null $versionNo
     * @param string|null $languageCode
     *
     * @return array
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function getSiteaccessesForLocation(
        Location $location,
        int $versionNo = null,
        string $languageCode = null
    ): array {
        return array_column($this->getSiteAccessesListForLocation($location, $versionNo, $languageCode), 'name');
    }

    /**
     * @return \Ibexa\Core\MVC\Symfony\SiteAccess[]
     */
    public function getSiteAccessesListForLocation(
        Location $location,
        ?int $versionNo = null,
        ?string $languageCode = null
    ): array {
        $contentInfo = $location->getContentInfo();
        $versionInfo = $this->contentService->loadVersionInfo($contentInfo, $versionNo);
        $languageCode = $languageCode ?? $contentInfo->mainLanguageCode;

        $eligibleSiteAccesses = [];
        /** @var \Ibexa\Core\MVC\Symfony\SiteAccess $siteAccess */
        foreach ($this->siteAccessService->getAll() as $siteAccess) {
            $context = new SiteaccessPreviewVoterContext($location, $versionInfo, $siteAccess->name, $languageCode);
            foreach ($this->siteAccessPreviewVoters as $siteAccessPreviewVoter) {
                if ($siteAccessPreviewVoter->vote($context)) {
                    $eligibleSiteAccesses[] = $siteAccess;
                    break;
                }
            }
        }

        return $eligibleSiteAccesses;
    }

    public function getSiteAccessesListForContent(Content $content): array
    {
        $versionInfo = $content->getVersionInfo();
        $contentInfo = $versionInfo->getContentInfo();

        if ($versionInfo->isDraft()) {
            // nonpublished content should use parent location instead because location doesn't exist yet
            $eligibleLocations = $this->locationService->loadParentLocationsForDraftContent($versionInfo);
        } else {
            $eligibleLocations = $this->locationService->loadLocations($contentInfo);
        }

        $eligibleLanguages = $versionInfo->getLanguages();

        $siteAccesses = [];
        foreach ($eligibleLocations as $location) {
            foreach ($eligibleLanguages as $language) {
                $siteAccesses = array_merge(
                    $this->getSiteAccessesListForLocation($location, null, $language->languageCode),
                    $siteAccesses
                );
            }
        }

        return array_unique($siteAccesses);
    }

    public function getSiteAccessesList(): array
    {
        return iterator_to_array($this->siteAccessService->getAll());
    }
}

class_alias(SiteaccessResolver::class, 'EzSystems\EzPlatformAdminUi\Siteaccess\SiteaccessResolver');
