<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Siteaccess;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;

final class SiteaccessPreviewVoterContext
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location */
    private $location;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo */
    private $versionInfo;

    /** @var string */
    private $siteaccess;

    /** @var array<string> */
    private $languageCodes;

    /**
     * @param array<int, string> $languageCodes
     */
    public function __construct(
        Location $location,
        VersionInfo $versionInfo,
        string $siteaccess,
        array $languageCodes
    ) {
        $this->location = $location;
        $this->versionInfo = $versionInfo;
        $this->siteaccess = $siteaccess;
        $this->languageCodes = $languageCodes;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location
     */
    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * @return string
     */
    public function getSiteaccess(): string
    {
        return $this->siteaccess;
    }

    /**
     * @return array<int, string>
     */
    public function getLanguageCodes(): array
    {
        return $this->languageCodes;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo
     */
    public function getVersionInfo(): VersionInfo
    {
        return $this->versionInfo;
    }
}

class_alias(SiteaccessPreviewVoterContext::class, 'EzSystems\EzPlatformAdminUi\Siteaccess\SiteaccessPreviewVoterContext');
