<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Pagination\Pagerfanta;

use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\Contracts\Core\Repository\BookmarkService;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Pagerfanta\Adapter\AdapterInterface;

class BookmarkAdapter implements AdapterInterface
{
    /** @var BookmarkService */
    private $bookmarkService;

    /** @var DatasetFactory */
    private $datasetFactory;

    /**
     * @param BookmarkService $bookmarkService
     * @param DatasetFactory $datasetFactory
     */
    public function __construct(
        BookmarkService $bookmarkService,
        DatasetFactory $datasetFactory
    ) {
        $this->bookmarkService = $bookmarkService;
        $this->datasetFactory = $datasetFactory;
    }

    /**
     * Returns the number of results.
     *
     * @return int the number of results
     *
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
     */
    public function getSlice(
        $offset,
        $length
    ) {
        return $this->datasetFactory
            ->bookmarks()
            ->load($offset, $length)
            ->getBookmarks();
    }
}

class_alias(BookmarkAdapter::class, 'EzSystems\EzPlatformAdminUi\Pagination\Pagerfanta\BookmarkAdapter');
