<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;

class ContentLocationAddData
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null */
    protected $contentInfo;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location[] */
    protected $newLocations;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null $currentLocation
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
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null
     */
    public function getContentInfo(): ?ContentInfo
    {
        return $this->contentInfo;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null $contentInfo
     */
    public function setContentInfo(?ContentInfo $contentInfo)
    {
        $this->contentInfo = $contentInfo;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
     */
    public function getNewLocations(): array
    {
        return $this->newLocations;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[] $newLocations
     */
    public function setNewLocations(array $newLocations)
    {
        $this->newLocations = $newLocations;
    }
}

class_alias(ContentLocationAddData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\Content\Location\ContentLocationAddData');
