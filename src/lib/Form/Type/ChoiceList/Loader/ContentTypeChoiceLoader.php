<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Type\ChoiceList\Loader;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

class ContentTypeChoiceLoader implements ChoiceLoaderInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    protected $contentTypeService;

    /** @var \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    private $userLanguagePreferenceProvider;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     * @param \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider
     */
    public function __construct(ContentTypeService $contentTypeService, UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider)
    {
        $this->contentTypeService = $contentTypeService;
        $this->userLanguagePreferenceProvider = $userLanguagePreferenceProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoiceList(): array
    {
        $contentTypesList = [];
        $preferredLanguages = $this->userLanguagePreferenceProvider->getPreferredLanguages();
        $contentTypeGroups = $this->contentTypeService->loadContentTypeGroups($preferredLanguages);
        foreach ($contentTypeGroups as $contentTypeGroup) {
            $contentTypes = $this->contentTypeService->loadContentTypes($contentTypeGroup, $preferredLanguages);
            usort($contentTypes, static function (ContentType $contentType1, ContentType $contentType2) {
                return strnatcasecmp($contentType1->getName(), $contentType2->getName());
            });

            $contentTypesList[$contentTypeGroup->identifier] = $contentTypes;
        }

        return $contentTypesList;
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoiceList($value = null)
    {
        $choices = $this->getChoiceList();

        return new ArrayChoiceList($choices, $value);
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

class_alias(ContentTypeChoiceLoader::class, 'EzSystems\EzPlatformAdminUi\Form\Type\ChoiceList\Loader\ContentTypeChoiceLoader');
