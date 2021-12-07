<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Siteaccess;

use Ibexa\Contracts\Core\Repository\ContentService;
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

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param iterable $siteaccessPreviewVoters
     * @param array $siteAccesses
     */
    public function __construct(
        ContentService $contentService,
        iterable $siteaccessPreviewVoters,
        SiteAccessService $siteAccessService
    ) {
        $this->contentService = $contentService;
        $this->siteAccessPreviewVoters = $siteaccessPreviewVoters;
        $this->siteAccessService = $siteAccessService;
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
        return $this->getSiteAccessList(
            $this->getSiteAccessesListForLocation($location, $versionNo, $languageCode)
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

    public function getSiteaccesses(): array
    {
        $siteAccessList = iterator_to_array($this->siteAccessService->getAll());

        return $this->getSiteAccessList($siteAccessList);
    }

    /**
     * @return string[]
     */
    private function getSiteAccessList(array $siteAccessList): array
    {
        return array_column(
            $siteAccessList,
            'name'
        );
    }
}

class_alias(SiteaccessResolver::class, 'EzSystems\EzPlatformAdminUi\Siteaccess\SiteaccessResolver');
