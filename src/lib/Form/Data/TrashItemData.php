<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Data;

use Ibexa\Contracts\Core\Repository\Values\Content\TrashItem;
use Ibexa\Contracts\Core\Repository\Values\Content\TrashItem as APITrashItem;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\User\User;

/**
 * @todo This class cannot be a part of Form/ namespace, it should be moved to UI/Value.
 */
class TrashItemData
{
    protected TrashItem $location;

    protected ?ContentType $contentType;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location[] */
    protected array $ancestors;

    private ?User $creator;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[] $ancestors
     */
    public function __construct(
        APITrashItem $location,
        ContentType $contentType = null,
        array $ancestors = [],
        ?User $creator = null
    ) {
        $this->location = $location;
        $this->contentType = $contentType;
        $this->ancestors = $ancestors;
        $this->creator = $creator;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\TrashItem
     */
    public function getLocation(): APITrashItem
    {
        return $this->location;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\TrashItem $location
     */
    public function setLocation(APITrashItem $location): void
    {
        $this->location = $location;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType
     */
    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     */
    public function setContentType(ContentType $contentType): void
    {
        $this->contentType = $contentType;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
     */
    public function getAncestors(): array
    {
        return $this->ancestors;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[] $ancestors
     */
    public function setAncestors(array $ancestors): void
    {
        $this->ancestors = $ancestors;
    }

    public function isParentInTrash(): bool
    {
        $lastAncestor = end($this->ancestors);

        return $this->location->path !== array_merge($lastAncestor->path, [(string)$this->location->id]);
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }
}
