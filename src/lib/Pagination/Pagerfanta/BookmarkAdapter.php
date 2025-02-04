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

/**
 * @implements \Pagerfanta\Adapter\AdapterInterface<\Ibexa\AdminUi\UI\Value\Location\Bookmark>
 */
class BookmarkAdapter implements AdapterInterface
{
    private BookmarkService $bookmarkService;

    private DatasetFactory $datasetFactory;

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
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function getNbResults(): int
    {
        /** @var int<0, max> */
        return $this->bookmarkService->loadBookmarks()->totalCount;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function getSlice(int $offset, int $length): iterable
    {
        return $this->datasetFactory
            ->bookmarks()
            ->load($offset, $length)
            ->getBookmarks();
    }
}
