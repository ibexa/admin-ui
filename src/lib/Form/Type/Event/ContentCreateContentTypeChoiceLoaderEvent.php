<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Form\Type\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class ContentCreateContentTypeChoiceLoaderEvent extends Event
{
    public const RESOLVE_CONTENT_TYPES = 'admin_ui.content_create.content_type_resolve';

    /** @var array<string, array<\Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType>> */
    private array $contentTypeGroups;

    public function __construct(array $contentTypeGroups)
    {
        $this->contentTypeGroups = $contentTypeGroups;
    }

    /**
     * @return array<string, array<\Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType>>
     */
    public function getContentTypeGroups(): array
    {
        return $this->contentTypeGroups;
    }

    /**
     * @param array<\Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType> $contentTypes
     */
    public function addContentTypeGroup(string $name, array $contentTypes): void
    {
        $this->contentTypeGroups[$name] = $contentTypes;
    }
}
