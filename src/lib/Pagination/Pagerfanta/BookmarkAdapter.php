<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\Contracts\Core\Repository\BookmarkService;
use Pagerfanta\Adapter\AdapterInterface;

class BookmarkAdapter implements AdapterInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\BookmarkService */
    private $bookmarkService;

    /** @var \Ibexa\AdminUi\UI\Dataset\DatasetFactory */
    private $datasetFactory;

    /**
     * @param \Ibexa\Contracts\Core\Repository\BookmarkService $bookmarkService
     * @param \Ibexa\AdminUi\UI\Dataset\DatasetFactory $datasetFactory
     */
    public function __construct(BookmarkService $bookmarkService, DatasetFactory $datasetFactory)
    {
        $this->bookmarkService = $bookmarkService;
        $this->datasetFactory = $datasetFactory;
    }

    /**
     * Returns the number of results.
     *
     * @return int the number of results
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function getNbResults()
    {
        return $this->bookmarkService->loadBookmarks()->totalCount;
    }

    /**
     * Returns an slice of the results.
     *
     * @param int $offset the offset
     * @param int $length the length
     *
     * @return array|\Traversable the slice
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function getSlice($offset, $length)
    {
        return $this->datasetFactory
            ->bookmarks()
            ->load($offset, $length)
            ->getBookmarks();
    }
}
