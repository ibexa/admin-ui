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

class LocationsDataset
{
    protected LocationService $locationService;

    protected ValueFactory $valueFactory;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location[] */
    protected $data;

    /**
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     * @param \Ibexa\AdminUi\UI\Value\ValueFactory $valueFactory
     */
    public function __construct(LocationService $locationService, ValueFactory $valueFactory)
    {
        $this->locationService = $locationService;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[] $locations
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
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
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
     */
    public function getLocations(): array
    {
        return $this->data;
    }
}
