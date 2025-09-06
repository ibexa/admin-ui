<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Event;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Symfony\Contracts\EventDispatcher\Event;

final class ResolveVersionPreviewUrlEvent extends Event
{
    private ?string $previewUrl = null;

    public function __construct(
        private readonly VersionInfo $versionInfo,
        private readonly Language $language,
        private readonly Location $location,
        private readonly SiteAccess $siteAccess
    ) {
    }

    public function getVersionInfo(): VersionInfo
    {
        return $this->versionInfo;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getSiteAccess(): SiteAccess
    {
        return $this->siteAccess;
    }

    public function getPreviewUrl(): ?string
    {
        return $this->previewUrl;
    }

    public function setPreviewUrl(?string $previewUrl): void
    {
        $this->previewUrl = $previewUrl;
    }
}
