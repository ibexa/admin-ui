<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\URLWildcardService;
use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard;
use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard\URLWildcardQuery;
use Pagerfanta\Adapter\AdapterInterface;

final class URLWildcardAdapter implements AdapterInterface
{
    /** @var URLWildcardService */
    private $urlWildcardService;

    /** @var int */
    private $nbResults;

    /** @var URLWildcardQuery */
    private $query;

    public function __construct(
        URLWildcardQuery $query,
        URLWildcardService $urlWildcardService
    ) {
        $this->query = $query;
        $this->urlWildcardService = $urlWildcardService;
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnauthorizedException
     */
    public function getNbResults(): int
    {
        $query = clone $this->query;
        $query->offset = 0;
        $query->limit = 0;

        return $this->urlWildcardService->findUrlWildcards($query)->totalCount;
    }

    /**
     * {@inheritdoc}
     *
     * @return URLWildcard[]
     *
     * @throws UnauthorizedException
     */
    public function getSlice(
        $offset,
        $length
    ): array {
        $query = clone $this->query;
        $query->offset = $offset;
        $query->limit = $length;
        $query->performCount = false;

        return $this->urlWildcardService->findUrlWildcards($query)->items;
    }
}
