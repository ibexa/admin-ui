<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\CustomUrl;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;

class CustomUrlRemoveData
{
    /** @var Location|null */
    private $location;

    /** @var array */
    private $urlAliases;

    /**
     * @param Location|null $location
     * @param array $urlAliases
     */
    public function __construct(
        ?Location $location = null,
        array $urlAliases = []
    ) {
        $this->location = $location;
        $this->urlAliases = $urlAliases;
    }

    /**
     * @return Location|null
     */
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    /**
     * @param Location|null $location
     *
     * @return CustomUrlRemoveData
     */
    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return array
     */
    public function getUrlAliases(): array
    {
        return $this->urlAliases;
    }

    /**
     * @param array $urlAliases
     *
     * @return CustomUrlRemoveData
     */
    public function setUrlAliases(array $urlAliases): self
    {
        $this->urlAliases = $urlAliases;

        return $this;
    }
}

class_alias(CustomUrlRemoveData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\Content\CustomUrl\CustomUrlRemoveData');
