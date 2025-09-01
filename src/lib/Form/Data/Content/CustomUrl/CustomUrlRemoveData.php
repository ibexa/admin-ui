<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\CustomUrl;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;

final class CustomUrlRemoveData
{
    /**
     * @param \Ibexa\AdminUi\UI\Value\Content\UrlAlias[] $urlAliases
     */
    public function __construct(
        private ?Location $location = null,
        private array $urlAliases = []
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

    /**
     * @return \Ibexa\AdminUi\UI\Value\Content\UrlAlias[]
     */
    public function getUrlAliases(): array
    {
        return $this->urlAliases;
    }

    /**
     * @param \Ibexa\AdminUi\UI\Value\Content\UrlAlias[] $urlAliases
     */
    public function setUrlAliases(array $urlAliases): self
    {
        $this->urlAliases = $urlAliases;

        return $this;
    }
}
