<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\URLService;
use Ibexa\Contracts\Core\Repository\Values\URL\URLQuery;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @implements \Pagerfanta\Adapter\AdapterInterface<\Ibexa\Contracts\Core\Repository\Values\URL\URL>
 */
class URLSearchAdapter implements AdapterInterface
{
    private URLQuery $query;

    private URLService $urlService;

    /**
     * UrlSearchAdapter constructor.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\URL\URLQuery $query
     * @param \Ibexa\Contracts\Core\Repository\URLService $urlService
     */
    public function __construct(URLQuery $query, URLService $urlService)
    {
        $this->query = $query;
        $this->urlService = $urlService;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function getNbResults(): int
    {
        $query = clone $this->query;
        $query->offset = 0;
        $query->limit = 0;

        /** @phpstan-var int<0, max> */
        return $this->urlService->findUrls($query)->totalCount;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\URL\URL[]
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function getSlice(int $offset, int $length): array
    {
        $query = clone $this->query;
        $query->offset = $offset;
        $query->limit = $length;
        $query->performCount = false;

        return $this->urlService->findUrls($query)->items;
    }
}
