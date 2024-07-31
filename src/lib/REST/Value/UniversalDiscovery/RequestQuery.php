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
    private int $locationId;

    private int $offset;

    private int $limit;

    private SortClause $sortClause;

    private int $rootLocationId;

    public function __construct(
        int $locationId,
        int $offset,
        int $limit,
        SortClause $sortClause,
        int $rootLocationId
    ) {
        $this->locationId = $locationId;
        $this->offset = $offset;
        $this->limit = $limit;
        $this->sortClause = $sortClause;
        $this->rootLocationId = $rootLocationId;
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
