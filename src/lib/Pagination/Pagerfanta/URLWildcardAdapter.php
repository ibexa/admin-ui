<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\Contracts\Core\Repository\URLWildcardService;
use Pagerfanta\Adapter\AdapterInterface;

final class URLWildcardAdapter implements AdapterInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\URLWildcardService */
    private $urlWildcardService;

    /** @var int */
    private $nbResults;

    public function __construct(URLWildcardService $urlWildcardService)
    {
        $this->urlWildcardService = $urlWildcardService;
    }

    /**
     * Returns the number of results.
     *
     * @return int the number of results
     */
    public function getNbResults(): int
    {
        if ($this->nbResults !== null) {
            return $this->nbResults;
        }

        return $this->nbResults = $this->urlWildcardService->countAll();
    }

    /**
     * Returns a slice of the results.
     *
     * @param int $offset the offset
     * @param int $length the length
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard[]
     */
    public function getSlice($offset, $length): array
    {
        return $this->urlWildcardService->loadAll($offset, $length);
    }
}
