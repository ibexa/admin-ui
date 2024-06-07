<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\Event;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Contracts\EventDispatcher\Event;

final class ContentCreateContentTypeChoiceLoaderEvent extends Event
{
    public const RESOLVE_CONTENT_TYPES = 'admin_ui.content_create.content_type_resolve';

    /** @var array<string, array<\Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType>> */
    private array $contentTypeGroups;

    private ?Location $targetLocation;

    /**
     * @param array<string, array<\Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType>> $contentTypeGroups
     */
    public function __construct(array $contentTypeGroups, ?Location $targetLocation)
    {
        $this->contentTypeGroups = $contentTypeGroups;
        $this->targetLocation = $targetLocation;
    }

    /**
     * @return array<string, array<\Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType>>
     */
    public function getContentTypeGroups(): array
    {
        return $this->contentTypeGroups;
    }

    /**
     * @param array<string, array<\Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType>> $contentTypeGroups
     */
    public function setContentTypeGroups(array $contentTypeGroups): void
    {
        $this->contentTypeGroups = $contentTypeGroups;
    }

    /**
     * @param array<\Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType> $contentTypes
     */
    public function addContentTypeGroup(string $name, array $contentTypes): void
    {
        $this->contentTypeGroups[$name] = $contentTypes;
    }

    public function removeContentTypeGroup(string $name): void
    {
        unset($this->contentTypeGroups[$name]);
    }

    public function getTargetLocation(): ?Location
    {
        return $this->targetLocation;
    }
}
