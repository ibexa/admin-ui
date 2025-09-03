<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\URLWildcardService;
use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard\URLWildcardQuery;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @implements \Pagerfanta\Adapter\AdapterInterface<\Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard>
 */
final readonly class URLWildcardAdapter implements AdapterInterface
{
    public function __construct(
        private URLWildcardQuery $query,
        private URLWildcardService $urlWildcardService
    ) {
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function getNbResults(): int
    {
        $query = clone $this->query;
        $query->offset = 0;
        $query->limit = 0;

        /** @phpstan-var int<0, max> */
        return $this->urlWildcardService->findUrlWildcards($query)->totalCount;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard[]
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function getSlice(int $offset, int $length): array
    {
        $query = clone $this->query;
        $query->offset = $offset;
        $query->limit = $length;
        $query->performCount = false;

        return $this->urlWildcardService->findUrlWildcards($query)->items;
    }
}
