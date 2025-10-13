<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Search;

use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\User\User;

final class TrashSearchData
{
    /**
     * @param array<mixed>|null $trashedInterval
     * @param array<mixed>|null $sort
     */
    public function __construct(
        private ?int $limit = 10,
        private ?int $page = 1,
        private ?Section $section = null,
        private ?ContentType $contentType = null,
        private ?string $trashed = null,
        private ?array $trashedInterval = [],
        private ?User $creator = null,
        private ?string $contentName = null,
        private ?array $sort = ['field' => 'trashed', 'direction' => 0]
    ) {
    }

    public function getTrashed(): ?string
    {
        return $this->trashed;
    }

    public function setTrashed(?string $trashed): void
    {
        $this->trashed = $trashed;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): void
    {
        $this->limit = $limit;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): void
    {
        $this->page = $page;
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(?Section $section): void
    {
        $this->section = $section;
    }

    public function getContentType(): ?ContentType
    {
        return $this->contentType;
    }

    public function setContentType(?ContentType $contentType): void
    {
        $this->contentType = $contentType;
    }

    /**
     * @return array<mixed>|null
     */
    public function getTrashedInterval(): ?array
    {
        return $this->trashedInterval;
    }

    /**
     * @param array<mixed>|null $trashedInterval
     */
    public function setTrashedInterval(?array $trashedInterval): void
    {
        $this->trashedInterval = $trashedInterval;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): void
    {
        $this->creator = $creator;
    }

    public function getContentName(): ?string
    {
        return $this->contentName;
    }

    public function setContentName(?string $contentName): void
    {
        $this->contentName = $contentName;
    }

    /**
     * @return array<mixed>|null
     */
    public function getSort(): ?array
    {
        return $this->sort;
    }

    /**
     * @param array<mixed>|null $sort
     */
    public function setSort(?array $sort): void
    {
        $this->sort = $sort;
    }

    public function isFiltered(): bool
    {
        $contentType = $this->getContentType();
        $section = $this->getSection();
        $trashed = $this->getTrashedInterval();
        $creator = $this->getCreator();
        $contentName = $this->getContentName();

        return
            null !== $contentType ||
            null !== $section ||
            null !== $creator ||
            null !== $contentName ||
            !empty($trashed);
    }
}
