<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\TrashService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @implements \Pagerfanta\Adapter\AdapterInterface<\Ibexa\Contracts\Core\Repository\Values\Content\TrashItem>
 */
class TrashItemAdapter implements AdapterInterface
{
    private Query $query;

    private TrashService $trashService;

    /** @phpstan-var int<0, max> */
    private int $nbResults;

    public function __construct(Query $query, TrashService $trashService)
    {
        $this->query = $query;
        $this->trashService = $trashService;
    }

    public function getNbResults(): int
    {
        if (isset($this->nbResults)) {
            return $this->nbResults;
        }

        $countQuery = clone $this->query;
        $countQuery->limit = 0;

        return $this->nbResults = $this->trashService->findTrashItems($countQuery)->totalCount;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ValueObject[]
     */
    public function getSlice(int $offset, int $length): array
    {
        $query = clone $this->query;
        $query->offset = $offset;
        $query->limit = $length;
        $query->performCount = false;

        $trashItems = $this->trashService->findTrashItems($query);

        if (!isset($this->nbResults)) {
            $this->nbResults = $trashItems->totalCount;
        }

        return $trashItems->items;
    }
}
