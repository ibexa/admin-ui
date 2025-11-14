<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

class LocationsDataset
{
    /** @var LocationService */
    protected $locationService;

    /** @var ValueFactory */
    protected $valueFactory;

    /** @var Location[] */
    protected $data;

    /**
     * @param LocationService $locationService
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        LocationService $locationService,
        ValueFactory $valueFactory
    ) {
        $this->locationService = $locationService;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @param ContentInfo $contentInfo
     *
     * @return LocationsDataset
     */
    public function load(ContentInfo $contentInfo): self
    {
        $this->data = array_map(
            [$this->valueFactory, 'createLocation'],
            $this->locationService->loadLocations($contentInfo)
        );
        $this->data = $this->prioritizeMainLocation($this->data);

        return $this;
    }

    /**
     * @param Location[] $locations
     *
     * @return Location[]
     */
    protected function prioritizeMainLocation(array $locations): array
    {
        foreach ($locations as $key => $location) {
            if ($location->main) {
                unset($locations[$key]);
                array_unshift($locations, $location);
                break;
            }
        }

        return $locations;
    }

    /**
     * @return Location[]
     */
    public function getLocations(): array
    {
        return $this->data;
    }
}

class_alias(LocationsDataset::class, 'EzSystems\EzPlatformAdminUi\UI\Dataset\LocationsDataset');
