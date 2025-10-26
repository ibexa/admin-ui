<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

class ContentLocationAddData
{
    /** @var ContentInfo|null */
    protected $contentInfo;

    /** @var Location[] */
    protected $newLocations;

    /**
     * @param ContentInfo|null $currentLocation
     * @param array $newLocations
     */
    public function __construct(
        ?ContentInfo $currentLocation = null,
        array $newLocations = []
    ) {
        $this->contentInfo = $currentLocation;
        $this->newLocations = $newLocations;
    }

    /**
     * @return ContentInfo|null
     */
    public function getContentInfo(): ?ContentInfo
    {
        return $this->contentInfo;
    }

    /**
     * @param ContentInfo|null $contentInfo
     */
    public function setContentInfo(?ContentInfo $contentInfo)
    {
        $this->contentInfo = $contentInfo;
    }

    /**
     * @return Location[]
     */
    public function getNewLocations(): array
    {
        return $this->newLocations;
    }

    /**
     * @param Location[] $newLocations
     */
    public function setNewLocations(array $newLocations)
    {
        $this->newLocations = $newLocations;
    }
}

class_alias(ContentLocationAddData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\Content\Location\ContentLocationAddData');
