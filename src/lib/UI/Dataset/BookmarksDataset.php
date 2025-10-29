<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\UI\Value\Location\Bookmark;
use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\BookmarkService;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

class BookmarksDataset
{
    /** @var BookmarkService */
    private $bookmarkService;

    /** @var ValueFactory */
    private $valueFactory;

    /** @var Bookmark[] */
    private $data;

    /**
     * @param BookmarkService $bookmarkService
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        BookmarkService $bookmarkService,
        ValueFactory $valueFactory
    ) {
        $this->bookmarkService = $bookmarkService;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return BookmarksDataset
     *
     * @throws InvalidArgumentException
     */
    public function load(
        int $offset = 0,
        int $limit = 25
    ): self {
        $this->data = array_map(
            function (Location $location) {
                return $this->valueFactory->createBookmark($location);
            },
            $this->bookmarkService->loadBookmarks($offset, $limit)->items
        );

        return $this;
    }

    /**
     * @return Bookmark[]
     */
    public function getBookmarks(): array
    {
        return $this->data;
    }
}

class_alias(BookmarksDataset::class, 'EzSystems\EzPlatformAdminUi\UI\Dataset\BookmarksDataset');
