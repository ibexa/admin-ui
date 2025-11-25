<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\UI\Value as UIValue;
use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\BookmarkService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

final class BookmarksDataset
{
    /** @var \Ibexa\AdminUi\UI\Value\Location\Bookmark[] */
    private ?array $data = null;

    public function __construct(
        private readonly BookmarkService $bookmarkService,
        private readonly ValueFactory $valueFactory
    ) {
    }

    public function load(int $offset = 0, int $limit = 25): self
    {
        $this->data = array_map(
            function (Location $location): UIValue\Location\Bookmark {
                return $this->valueFactory->createBookmark($location);
            },
            $this->bookmarkService->loadBookmarks($offset, $limit)->items
        );

        return $this;
    }

    /**
     * @return \Ibexa\AdminUi\UI\Value\Location\Bookmark[]
     */
    public function getBookmarks(): array
    {
        return $this->data ?? [];
    }
}
