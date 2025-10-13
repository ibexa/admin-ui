<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\UniversalDiscovery;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Rest\Value;

final class RequestQuery extends Value
{
    public function __construct(
        private readonly int $locationId,
        private readonly int $offset,
        private readonly int $limit,
        private readonly SortClause $sortClause,
        private readonly int $rootLocationId
    ) {
    }

    public function getLocationId(): int
    {
        return $this->locationId;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getSortClause(): SortClause
    {
        return $this->sortClause;
    }

    public function getRootLocationId(): int
    {
        return $this->rootLocationId;
    }
}
