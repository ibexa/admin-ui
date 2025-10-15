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

readonly class SiteaccessResolver implements SiteaccessResolverInterface
{
    /**
     * @param iterable<\Ibexa\AdminUi\Siteaccess\SiteaccessPreviewVoterInterface> $siteAccessPreviewVoters
     */
    public function __construct(
        private ContentService $contentService,
        private iterable $siteAccessPreviewVoters,
        private SiteAccessService $siteAccessService,
        private LocationService $locationService
    ) {
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
        $languageCode = $languageCode ?? $contentInfo->getMainLanguageCode();

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
            // non-published content should use parent location instead because location doesn't exist yet
            $eligibleLocations = $this->locationService->loadParentLocationsForDraftContent($versionInfo);
        } else {
            $eligibleLocations = $this->locationService->loadLocations($contentInfo);
        }

        $eligibleLanguages = $versionInfo->getLanguages();

        $siteAccesses = [];
        foreach ($eligibleLocations as $location) {
            foreach ($eligibleLanguages as $language) {
                $siteAccesses = array_merge(
                    $this->getSiteAccessesListForLocation(
                        $location,
                        null,
                        $language->getLanguageCode()
                    ),
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
