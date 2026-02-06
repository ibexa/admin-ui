<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Location;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Specification\AbstractSpecification;

/**
 * @internal
 */
final class IsWithinCopySubtreeLimit extends AbstractSpecification
{
    public function __construct(
        private readonly int $copyLimit,
        private readonly LocationService $locationService
    ) {
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $item
     */
    public function isSatisfiedBy(mixed $item): bool
    {
        if ($this->copyLimit === -1) {
            return true;
        }

        if ($this->copyLimit === 0 || !$this->isContainer($item)) {
            return false;
        }

        return $this->copyLimit >= $this->locationService->getSubtreeSize($item, $this->copyLimit + 1);
    }

    private function isContainer(Location $location): bool
    {
        return $location->getContentInfo()->getContentType()->isContainer();
    }
}
