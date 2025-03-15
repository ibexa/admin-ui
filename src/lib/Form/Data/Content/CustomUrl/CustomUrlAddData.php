<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\CustomUrl;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

class CustomUrlAddData
{
    private ?Location $location;

    private ?string $path;

    private ?Language $language;

    private bool $redirect;

    private bool $siteRoot;

    private ?string $siteAccess;

    public function __construct(
        ?Location $location = null,
        ?string $path = null,
        ?Language $language = null,
        bool $redirect = true,
        bool $siteRoot = true,
        ?string $siteAccess = null
    ) {
        $this->location = $location;
        $this->path = $path;
        $this->language = $language;
        $this->redirect = $redirect;
        $this->siteRoot = $siteRoot;
        $this->siteAccess = $siteAccess;
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
     * @return CustomUrlAddData
     */
    public function setLocation(?Location $location): self
    {
        $this->location = $location;

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
     * @return CustomUrlAddData
     */
    public function setLanguage(?Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param string|null $path
     *
     * @return CustomUrlAddData
     */
    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRedirect(): bool
    {
        return $this->redirect;
    }

    /**
     * @param bool $redirect
     *
     * @return CustomUrlAddData
     */
    public function setRedirect(bool $redirect): self
    {
        $this->redirect = $redirect;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSiteRoot(): bool
    {
        return $this->siteRoot;
    }

    /**
     * @param bool $siteRoot
     *
     * @return CustomUrlAddData
     */
    public function setSiteRoot(bool $siteRoot): self
    {
        $this->siteRoot = $siteRoot;

        return $this;
    }

    public function getSiteAccess(): ?string
    {
        return $this->siteAccess;
    }

    public function setSiteAccess(?string $siteAccess): self
    {
        $this->siteAccess = $siteAccess;

        return $this;
    }
}
