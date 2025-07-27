<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Bookmark;

use Symfony\Component\Validator\Constraints as Assert;

class BookmarkRemoveData
{
    /**
     * @var array<int, false>
     */
    #[Assert\NotBlank]
    public array $bookmarks;

    /**
     * @param array<int, false> $bookmarks
     */
    public function __construct(array $bookmarks = [])
    {
        $this->bookmarks = $bookmarks;
    }

    /**
     * @return array<int, false>
     */
    public function getBookmarks(): array
    {
        return $this->bookmarks;
    }

    /**
     * @param array<int, false> $bookmarks
     */
    public function setBookmarks(array $bookmarks): void
    {
        $this->bookmarks = $bookmarks;
    }
}
