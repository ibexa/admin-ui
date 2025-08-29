<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data;

use Ibexa\Contracts\Core\Repository\Values\Content\TrashItem as APITrashItem;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\User\User;

/**
 * @todo This class cannot be a part of Form/ namespace, it should be moved to UI/Value.
 */
final class TrashItemData
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[] $ancestors
     */
    public function __construct(
        private APITrashItem $location,
        private ?ContentType $contentType = null,
        private array $ancestors = [],
        private readonly ?User $creator = null
    ) {
    }

    public function getLocation(): APITrashItem
    {
        return $this->location;
    }

    public function setLocation(APITrashItem $location): void
    {
        $this->location = $location;
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

        return $lastAncestor !== false
            && $this->location->path !== array_merge(
                $lastAncestor->path,
                [(string)$this->location->id]
            );
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }
}
