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
    private Location $location;

    private VersionInfo $versionInfo;

    private string $siteaccess;

    private string $languageCode;

    public function __construct(
        Location $location,
        VersionInfo $versionInfo,
        string $siteaccess,
        string $languageCode
    ) {
        $this->location = $location;
        $this->versionInfo = $versionInfo;
        $this->siteaccess = $siteaccess;
        $this->languageCode = $languageCode;
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
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo
     */
    public function getVersionInfo(): VersionInfo
    {
        return $this->versionInfo;
    }
}
