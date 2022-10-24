<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\Location;

use Ibexa\AdminUi\Specification\AbstractSpecification;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Filter\Filter;

/**
 * @internal
 */
class IsWithinCopySubtreeLimit extends AbstractSpecification
{
    /** @var int */
    private $copyLimit;

    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $locationService;

    public function __construct(
        int $copyLimit,
        LocationService $locationService
    ) {
        $this->copyLimit = $copyLimit;
        $this->locationService = $locationService;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $item
     *
     * @return bool
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     */
    public function isSatisfiedBy($item): bool
    {
        if ($this->copyLimit === -1) {
            return true;
        }

        if ($this->copyLimit === 0) {
            return false;
        }

        $filter = new Filter(new Criterion\Subtree($item->pathString));

        return $this->copyLimit >= $this->locationService->count($filter);
    }
}

class_alias(IsWithinCopySubtreeLimit::class, 'EzSystems\EzPlatformAdminUi\Specification\Location\IsWithinCopySubtreeLimit');
