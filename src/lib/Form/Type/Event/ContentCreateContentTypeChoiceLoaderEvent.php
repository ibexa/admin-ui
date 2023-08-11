<?php

namespace Ibexa\AdminUi\Form\Type\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class ContentCreateContentTypeChoiceLoaderEvent extends Event
{
    public const RESOLVE_CONTENT_TYPES = 'admin_ui.content_create.content_type_resolve';

    /** @var array<string, array> */
    private array $contentTypeGroups;

    public function __construct(array $contentTypeGroups) {
        $this->contentTypeGroups = $contentTypeGroups;
    }

    /**
     * @return array<string, array>
     */
    public function getContentTypeGroups(): array
    {
        return $this->contentTypeGroups;
    }

    /**
     * @param array<string, array> $contentTypeGroups
     */
    public function setContentTypeGroups(array $contentTypeGroups): void
    {
        $this->contentTypeGroups = $contentTypeGroups;
    }
}
