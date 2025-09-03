<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Siteaccess;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;

final readonly class SiteaccessPreviewVoterContext
{
    public function __construct(
        private Location $location,
        private VersionInfo $versionInfo,
        private string $siteaccess,
        private string $languageCode
    ) {
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getSiteaccess(): string
    {
        return $this->siteaccess;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    public function getVersionInfo(): VersionInfo
    {
        return $this->versionInfo;
    }
}
