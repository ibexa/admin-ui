<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ChoiceList\Loader;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

class ContentCreateContentTypeChoiceLoader implements ChoiceLoaderInterface
{
    /** @var \Ibexa\AdminUi\Form\Type\ChoiceList\Loader\ContentTypeChoiceLoader */
    private $contentTypeChoiceLoader;

    /** @var int[] */
    private $restrictedContentTypesIds;

    private $contentTypesGroups;

    /**
     * @param \Ibexa\AdminUi\Form\Type\ChoiceList\Loader\ContentTypeChoiceLoader $contentTypeChoiceLoader
     * @param array $restrictedContentTypesIds
     */
    public function __construct(
        ContentTypeChoiceLoader $contentTypeChoiceLoader,
        array $restrictedContentTypesIds
    ) {
        $this->contentTypeChoiceLoader = $contentTypeChoiceLoader;
        $this->restrictedContentTypesIds = $restrictedContentTypesIds;
        $this->contentTypesGroups = null;
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoiceList($value = null)
    {
        if ($this->contentTypesGroups === null) {
            $this->contentTypesGroups = $this->contentTypeChoiceLoader->getChoiceList();
        }        

        if (empty($this->restrictedContentTypesIds)) {
            return new ArrayChoiceList($this->contentTypesGroups, $value);
        }

        $contentTypesGroups = $this->contentTypesGroups;

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
