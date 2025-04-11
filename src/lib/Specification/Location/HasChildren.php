<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Location;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Specification\AbstractSpecification;

class HasChildren extends AbstractSpecification
{
    private LocationService $locationService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     */
    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $item
     *
     * @return bool
     */
    public function isSatisfiedBy($item): bool
    {
        $childCount = $this->locationService->getLocationChildCount($item);

        return 0 < $childCount;
    }
}
