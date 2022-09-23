<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Service\MetaFieldType;

use Ibexa\AdminUi\Config\AdminUiForms\ContentTypeFieldTypesResolverInterface;
use Ibexa\Bundle\AdminUi\DependencyInjection\Configuration\Parser\AdminUiForms;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use Ibexa\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
final class MetaFieldDefinitionService implements MetaFieldDefinitionServiceInterface
{
    private ConfigResolverInterface $configResolver;

    private ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver;

    private ContentTypeService $contentTypeService;

    private FieldsGroupsList $fieldsGroupsList;

    private LanguageService $languageService;

    private LocaleConverterInterface $localeConverter;

    private TranslatorInterface $translator;

    public function __construct(
        ConfigResolverInterface $configResolver,
        ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver,
        ContentTypeService $contentTypeService,
        FieldsGroupsList $fieldsGroupsList,
        LanguageService $languageService,
        LocaleConverterInterface $localeConverter,
        TranslatorInterface $translator
    ) {
        $this->configResolver = $configResolver;
        $this->contentTypeFieldTypesResolver = $contentTypeFieldTypesResolver;
        $this->contentTypeService = $contentTypeService;
        $this->fieldsGroupsList = $fieldsGroupsList;
        $this->languageService = $languageService;
        $this->localeConverter = $localeConverter;
        $this->translator = $translator;
    }

    public function addMetaFieldDefinitions(ValueObject $contentType, ?Language $language = null): void
    {
        $metaFieldTypes = $this->contentTypeFieldTypesResolver->getMetaFieldTypes();

        if (null === $language) {
            $language = $this->languageService->loadLanguage($this->languageService->getDefaultLanguageCode());
        }

        foreach ($metaFieldTypes as $metaFieldTypeIdentifier => $metaFieldTypeSettings) {
            $fieldGroup = $this->getDefaultMetaDataFieldTypeGroup() ?? $this->fieldsGroupsList->getDefaultGroup();

            if ($this->metaFieldDefinitionExists($metaFieldTypeIdentifier, $fieldGroup, $contentType)) {
                continue;
            }

            $fieldDefinitionCreateStruct = $this->createMetaFieldDefinitionCreateStruct(
                $metaFieldTypeIdentifier,
                $fieldGroup,
                $language,
                $metaFieldTypeSettings['position']
            );

            if ($contentType instanceof ContentTypeDraft) {
                $this->contentTypeService->addFieldDefinition($contentType, $fieldDefinitionCreateStruct);
            }

            if ($contentType instanceof ContentTypeCreateStruct) {
                $contentType->addFieldDefinition($fieldDefinitionCreateStruct);
            }
        }
    }

    public function createMetaFieldDefinitionCreateStruct(
        string $identifier,
        string $fieldGroup,
        Language $language,
        int $position
    ): FieldDefinitionCreateStruct {
        $fieldDefinitionCreateStruct = $this->contentTypeService->newFieldDefinitionCreateStruct(
            uniqid('field_'),
            $identifier
        );

        $fieldDefinitionCreateStruct->fieldGroup = $fieldGroup;
        $label = $this->translator->trans(/** @Ignore */
            $identifier . '.name',
            [],
            'fieldtypes',
            $this->localeConverter->convertToPOSIX($language->languageCode)
        );

        $fieldDefinitionCreateStruct->names = [
            $language->languageCode => $label,
        ];

        $fieldDefinitionCreateStruct->position = $position;

        return $fieldDefinitionCreateStruct;
    }

    public function metaFieldDefinitionExists(
        string $fieldTypeIdentifier,
        string $fieldTypeGroup,
        ValueObject $contentType
    ): bool {
        foreach ($contentType->fieldDefinitions as $fieldDefinition) {
            if (
                $fieldDefinition->fieldTypeIdentifier === $fieldTypeIdentifier
                && $fieldDefinition->fieldGroup === $fieldTypeGroup
            ) {
                return true;
            }
        }

        return false;
    }

    public function getDefaultMetaDataFieldTypeGroup(): ?string
    {
        if (!$this->configResolver->hasParameter(AdminUiForms::CONTENT_TYPE_DEFAULT_META_FIELD_TYPE_GROUP_PARAM)) {
            return null;
        }

        return $this->configResolver->getParameter(AdminUiForms::CONTENT_TYPE_DEFAULT_META_FIELD_TYPE_GROUP_PARAM);
    }
}
