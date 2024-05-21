<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\Draft;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;

/**
 * @todo Add validation. $language have to be validated that $versionInfo indeed has this language
 */
class ContentEditData
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location|null */
    protected $location;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null */
    protected $contentInfo;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo|null */
    protected $versionInfo;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Language|null */
    protected $language;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null $contentInfo
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo|null $versionInfo
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language|null $language
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     */
    public function __construct(
        ?ContentInfo $contentInfo = null,
        ?VersionInfo $versionInfo = null,
        ?Language $language = null,
        ?Location $location = null
    ) {
        $this->contentInfo = $contentInfo;
        $this->versionInfo = $versionInfo;
        $this->language = $language;
        $this->location = $location;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location|null
     */
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     *
     * @return self
     */
    public function setLocation(Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null
     */
    public function getContentInfo(): ?ContentInfo
    {
        return $this->contentInfo;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null $contentInfo
     *
     * @return self
     */
    public function setContentInfo(?ContentInfo $contentInfo): self
    {
        $this->contentInfo = $contentInfo;

        return $this;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo|null
     */
    public function getVersionInfo(): ?VersionInfo
    {
        return $this->versionInfo;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo|null $versionInfo
     *
     * @return self
     */
    public function setVersionInfo(?VersionInfo $versionInfo): self
    {
        $this->versionInfo = $versionInfo;

        return $this;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language|null
     */
    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language|null $language
     *
     * @return self
     */
    public function setLanguage(?Language $language): self
    {
        $this->language = $language;

        return $this;
    }
}
