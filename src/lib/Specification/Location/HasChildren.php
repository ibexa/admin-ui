<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Location;

use Ibexa\AdminUi\Specification\AbstractSpecification;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

class HasChildren extends AbstractSpecification
{
    /** @var LocationService */
    private $locationService;

    /**
     * @param LocationService $locationService
     */
    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * @param Location $item
     *
     * @return bool
     */
    public function isSatisfiedBy($item): bool
    {
        $childCount = $this->locationService->getLocationChildCount($item);

        return 0 < $childCount;
    }
}

class_alias(HasChildren::class, 'EzSystems\EzPlatformAdminUi\Specification\Location\HasChildren');
