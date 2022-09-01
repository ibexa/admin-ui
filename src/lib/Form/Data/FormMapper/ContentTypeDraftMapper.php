<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\FormMapper;

use Ibexa\AdminUi\Config\AdminUiForms\ContentTypeFieldTypesResolverInterface;
use Ibexa\AdminUi\Form\Data\ContentTypeData;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\Contracts\AdminUi\Event\FieldDefinitionMappingEvent;
use Ibexa\Contracts\AdminUi\Form\Data\FormMapper\FormDataMapperInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTypeDraftMapper implements FormDataMapperInterface
{
    private ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver;

    private ContentTypeService $contentTypeService;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcher;

    /** @var \Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList */
    private $fieldsGroupsList;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver,
        ContentTypeService $contentTypeService,
        EventDispatcherInterface $eventDispatcher,
        FieldsGroupsList $fieldsGroupsList
    ) {
        $this->contentTypeFieldTypesResolver = $contentTypeFieldTypesResolver;
        $this->contentTypeService = $contentTypeService;
        $this->eventDispatcher = $eventDispatcher;
        $this->fieldsGroupsList = $fieldsGroupsList;
    }

    /**
     * Maps a ValueObject from Ibexa content repository to a data usable as underlying form data (e.g. create/update struct).
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft|\Ibexa\Contracts\Core\Repository\Values\ValueObject $contentTypeDraft
     * @param array $params
     *
     * @return \Ibexa\AdminUi\Form\Data\ContentTypeData
     */
    public function mapToFormData(ValueObject $contentTypeDraft, array $params = [])
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);
        $params = $optionsResolver->resolve($params);

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Language $language */
        $language = $params['language'] ?? null;

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Language|null $baseLanguage */
        $baseLanguage = $params['baseLanguage'] ?? null;

        $contentTypeData = new ContentTypeData(['contentTypeDraft' => $contentTypeDraft]);
        if (!$contentTypeData->isNew()) {
            $contentTypeData->identifier = $contentTypeDraft->identifier;
        }

        $contentTypeData->remoteId = $contentTypeDraft->remoteId;
        $contentTypeData->urlAliasSchema = $contentTypeDraft->urlAliasSchema;
        $contentTypeData->nameSchema = $contentTypeDraft->nameSchema;
        $contentTypeData->isContainer = $contentTypeDraft->isContainer;
        $contentTypeData->mainLanguageCode = $contentTypeDraft->mainLanguageCode;
        $contentTypeData->defaultSortField = $contentTypeDraft->defaultSortField;
        $contentTypeData->defaultSortOrder = $contentTypeDraft->defaultSortOrder;
        $contentTypeData->defaultAlwaysAvailable = $contentTypeDraft->defaultAlwaysAvailable;
        $contentTypeData->names = $contentTypeDraft->getNames();
        $contentTypeData->descriptions = $contentTypeDraft->getDescriptions();

        $contentTypeData->languageCode = $language ? $language->languageCode : $contentTypeDraft->mainLanguageCode;

        if ($baseLanguage && $language) {
            $contentTypeData->names[$language->languageCode] = $contentTypeDraft->getName($baseLanguage->languageCode);
            $contentTypeData->descriptions[$language->languageCode] = $contentTypeDraft->getDescription($baseLanguage->languageCode);
        }

        $metaFieldTypeIdentifiers = $this->contentTypeFieldTypesResolver->getMetaFieldTypeIdentifiers();

        try {
            $contentType = $this->contentTypeService->loadContentType($contentTypeDraft->id);
        } catch (NotFoundException $exception) {
            $contentType = null;
        }

        foreach ($contentTypeDraft->fieldDefinitions as $fieldDef) {
            $isMetaFieldType = in_array($fieldDef->fieldTypeIdentifier, $metaFieldTypeIdentifiers, true);

            $enabled = $isMetaFieldType
                && null !== $contentType
                && null !== $contentType->getFieldDefinition($fieldDef->identifier);

            $fieldDefinitionData = new FieldDefinitionData([
                'fieldDefinition' => $fieldDef,
                'contentTypeData' => $contentTypeData,
                'enabled' => $enabled,
            ]);

            $event = new FieldDefinitionMappingEvent(
                $fieldDefinitionData,
                $baseLanguage,
                $language
            );

            $this->eventDispatcher->dispatch($event, FieldDefinitionMappingEvent::NAME);

            if (empty($fieldDefinitionData->fieldGroup)) {
                $fieldDefinitionData->fieldGroup = $this->fieldsGroupsList->getDefaultGroup();
            }

            if ($isMetaFieldType) {
                $contentTypeData->addMetaFieldDefinitionData($event->getFieldDefinitionData());
            } else {
                $contentTypeData->addFieldDefinitionData($event->getFieldDefinitionData());
            }
        }
        $contentTypeData->sortFieldDefinitions();

        return $contentTypeData;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $optionsResolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    private function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver
            ->setDefined(['language'])
            ->setDefined(['baseLanguage'])
            ->setAllowedTypes('baseLanguage', ['null', Language::class])
            ->setAllowedTypes('language', Language::class);
    }
}

class_alias(ContentTypeDraftMapper::class, 'EzSystems\EzPlatformAdminUi\Form\Data\FormMapper\ContentTypeDraftMapper');
