<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;

/**
 * Class responsible for generating PlatformUI configuration for Multi File Upload functionality.
 */
class ContentTypeMappings implements ProviderInterface
{
    private ContentTypeService $contentTypeService;

    /** @var array<string, mixed> */
    protected array $locationMappings = [];

    /** @var array<string, mixed> */
    protected array $defaultMappings = [];

    /** @var array<string, mixed> */
    protected array $fallbackContentType = [];

    protected int $maxFileSize = 0;

    /**
     * @param array<string, mixed> $locationMappings
     * @param array<string, mixed> $defaultMappings
     * @param array<string, mixed> $fallbackContentType
     */
    public function __construct(
        ContentTypeService $contentTypeService,
        array $locationMappings,
        array $defaultMappings,
        array $fallbackContentType,
        int $maxFileSize
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->locationMappings = $locationMappings;
        $this->defaultMappings = $defaultMappings;
        $this->fallbackContentType = $fallbackContentType;
        $this->maxFileSize = $maxFileSize;
    }

    /**
     * Returns configuration structure compatible with AdminUI.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $structure = [
            'locationMappings' => [],
            'defaultMappings' => [],
            'fallbackContentType' => $this->buildFallbackContentTypeStructure($this->fallbackContentType),
            'maxFileSize' => $this->maxFileSize,
        ];

        foreach ($this->locationMappings as $locationIdentifier => $locationConfiguration) {
            $structure['locationMappings'][$locationIdentifier] = [
                'contentTypeIdentifier' => $locationConfiguration['content_type_identifier'],
                'mimeTypeFilter' => $locationConfiguration['mime_type_filter'],
                'mappings' => [],
            ];

            foreach ($locationConfiguration['mappings'] as $mappingGroup) {
                $structure['locationMappings'][$locationIdentifier]['mappings'][] = $this->buildMappingGroupStructure($mappingGroup);
            }
        }

        foreach ($this->defaultMappings as $mappingGroup) {
            $structure['defaultMappings'][] = $this->buildMappingGroupStructure($mappingGroup);
        }

        return $structure;
    }

    /**
     * @param array<string> $mappingGroup
     *
     * @return array<string, mixed>
     */
    private function buildMappingGroupStructure(array $mappingGroup): array
    {
        $contentTypeIdentifier = $mappingGroup['content_type_identifier'];
        $contentFieldIdentifier = $mappingGroup['content_field_identifier'];

        return [
            'mimeTypes' => $mappingGroup['mime_types'],
            'contentTypeIdentifier' => $contentTypeIdentifier,
            'contentFieldIdentifier' => $contentFieldIdentifier,
            'nameFieldIdentifier' => $mappingGroup['name_field_identifier'],
            'maxFileSize' => $this->getContentTypeConfiguredMaxFileSize(
                $contentTypeIdentifier,
                $contentFieldIdentifier
            ),
        ];
    }

    /**
     * @param array<string> $fallbackContentType
     *
     * @return array<string, mixed>
     */
    private function buildFallbackContentTypeStructure(array $fallbackContentType): array
    {
        return [
            'contentTypeIdentifier' => $fallbackContentType['content_type_identifier'],
            'contentFieldIdentifier' => $fallbackContentType['content_field_identifier'],
            'nameFieldIdentifier' => $fallbackContentType['name_field_identifier'],
        ];
    }

    private function getContentTypeConfiguredMaxFileSize(
        string $contentTypeIdentifier,
        string $imageFieldTypeIdentifier
    ): int {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier(
            $contentTypeIdentifier
        );

        $imgFieldType = $contentType->getFieldDefinition($imageFieldTypeIdentifier);
        if ($imgFieldType === null) {
            return $this->maxFileSize;
        }

        $validatorConfig = $imgFieldType->getValidatorConfiguration();
        if (isset($validatorConfig['FileSizeValidator']['maxFileSize'])) {
            return (int)$validatorConfig['FileSizeValidator']['maxFileSize'] * 1024 * 1024;
        }

        return $this->maxFileSize;
    }
}

class_alias(ContentTypeMappings::class, 'EzSystems\EzPlatformAdminUi\UI\Config\Provider\ContentTypeMappings');
