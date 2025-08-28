<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\CustomUrl;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

final class CustomUrlAddData
{
    public function __construct(
        private ?Location $location = null,
        private ?string $path = null,
        private ?Language $language = null,
        private bool $redirect = true,
        private bool $siteRoot = true,
        private ?string $siteAccess = null
    ) {
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function isRedirect(): bool
    {
        return $this->redirect;
    }

    public function setRedirect(bool $redirect): self
    {
        $this->redirect = $redirect;

        return $this;
    }

    public function isSiteRoot(): bool
    {
        return $this->siteRoot;
    }

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
