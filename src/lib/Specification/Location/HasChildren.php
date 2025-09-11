<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Location;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Specification\AbstractSpecification;

final class HasChildren extends AbstractSpecification
{
    public function __construct(private readonly LocationService $locationService)
    {
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $item
     */
    public function isSatisfiedBy(mixed $item): bool
    {
        $childCount = $this->locationService->getLocationChildCount($item);

        return 0 < $childCount;
    }
}
