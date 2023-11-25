<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ChoiceList\Loader;

use Ibexa\AdminUi\Form\Type\Event\ContentCreateContentTypeChoiceLoaderEvent;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ContentCreateContentTypeChoiceLoader implements ChoiceLoaderInterface
{
    private ContentTypeChoiceLoader $contentTypeChoiceLoader;

    private EventDispatcherInterface $eventDispatcher;

    /** @var array<int> */
    private array $restrictedContentTypesIds;

    private ?Location $targetLocation = null;

    public function __construct(
        ContentTypeChoiceLoader $contentTypeChoiceLoader,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->contentTypeChoiceLoader = $contentTypeChoiceLoader;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setRestrictedContentTypeIds(array $restrictedContentTypeIds): self
    {
        $this->restrictedContentTypesIds = $restrictedContentTypeIds;

        return $this;
    }

    public function getTargetLocation(): ?Location
    {
        return $this->targetLocation;
    }

    public function setTargetLocation(?Location $targetLocation): self
    {
        $this->targetLocation = $targetLocation;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoiceList($value = null)
    {
        $contentTypesGroups = $this->contentTypeChoiceLoader->getChoiceList();

        $event = $this->eventDispatcher->dispatch(
            new ContentCreateContentTypeChoiceLoaderEvent($contentTypesGroups, $this->targetLocation),
            ContentCreateContentTypeChoiceLoaderEvent::RESOLVE_CONTENT_TYPES
        );

        $contentTypesGroups = $event->getContentTypeGroups();

        if (empty($this->restrictedContentTypesIds)) {
            return new ArrayChoiceList($contentTypesGroups, $value);
        }

        foreach ($contentTypesGroups as $group => $contentTypes) {
            $contentTypesGroups[$group] = array_filter($contentTypes, function (ContentType $contentType) {
                return \in_array($contentType->id, $this->restrictedContentTypesIds);
            });
        }

        return new ArrayChoiceList($contentTypesGroups, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoicesForValues(array $values, $value = null)
    {
        // Optimize
        $values = array_filter($values);
        if (empty($values)) {
            return [];
        }

        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    /**
     * {@inheritdoc}
     */
    public function loadValuesForChoices(array $choices, $value = null)
    {
        // Optimize
        $choices = array_filter($choices);
        if (empty($choices)) {
            return [];
        }

        // If no callable is set, choices are the same as values
        if (null === $value) {
            return $choices;
        }

        return $this->loadChoiceList($value)->getValuesForChoices($choices);
    }
}

class_alias(ContentCreateContentTypeChoiceLoader::class, 'EzSystems\EzPlatformAdminUi\Form\Type\ChoiceList\Loader\ContentCreateContentTypeChoiceLoader');
