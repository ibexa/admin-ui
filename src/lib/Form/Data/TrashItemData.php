<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Data;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\TrashItem;
use Ibexa\Contracts\Core\Repository\Values\Content\TrashItem as APITrashItem;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\User\User;

/**
 * @todo This class cannot be a part of Form/ namespace, it should be moved to UI/Value.
 */
class TrashItemData
{
    /** @var TrashItem */
    protected $location;

    /** @var ContentType */
    protected $contentType;

    /** @var Location[] */
    protected $ancestors;

    /** @var User */
    private $creator;

    /**
     * @param Location[] $ancestors
     */
    public function __construct(
        APITrashItem $location,
        ?ContentType $contentType = null,
        array $ancestors = [],
        ?User $creator = null
    ) {
        $this->location = $location;
        $this->contentType = $contentType;
        $this->ancestors = $ancestors;
        $this->creator = $creator;
    }

    public function getLocation(): APITrashItem
    {
        return $this->location;
    }

    public function setLocation(APITrashItem $location)
    {
        $this->location = $location;
    }

    public function getContentType(): ?ContentType
    {
        return $this->contentType;
    }

    public function setContentType(ContentType $contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @return Location[]
     */
    public function getAncestors(): array
    {
        return $this->ancestors;
    }

    /**
     * @param Location[] $ancestors
     */
    public function setAncestors(array $ancestors)
    {
        $this->ancestors = $ancestors;
    }

    public function isParentInTrash(): bool
    {
        $lastAncestor = end($this->ancestors);

        return $lastAncestor !== false && $this->location->path !== array_merge($lastAncestor->path, [(string)$this->location->id]);
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }
}

class_alias(TrashItemData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\TrashItemData');
