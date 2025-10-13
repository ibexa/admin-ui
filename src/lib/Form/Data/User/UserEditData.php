<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\User;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;

final class UserEditData
{
    public function __construct(
        private ?ContentInfo $contentInfo = null,
        private ?VersionInfo $versionInfo = null,
        private ?Language $language = null,
        private ?Location $location = null
    ) {
    }

    public function getContentInfo(): ?ContentInfo
    {
        return $this->contentInfo;
    }

    public function setContentInfo(?ContentInfo $contentInfo): void
    {
        $this->contentInfo = $contentInfo;
    }

    public function getVersionInfo(): ?VersionInfo
    {
        return $this->versionInfo;
    }

    public function setVersionInfo(?VersionInfo $versionInfo): void
    {
        $this->versionInfo = $versionInfo;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): void
    {
        $this->language = $language;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): void
    {
        $this->location = $location;
    }
}
