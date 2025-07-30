<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Util;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use LogicException;

final class ContentTypeFieldsExtractor implements ContentTypeFieldsExtractorInterface
{
    private ContentTypeFieldsExpressionParserInterface $expressionParser;

    private ContentTypeService $contentTypeService;

    public function __construct(
        ContentTypeFieldsExpressionParserInterface $expressionParser,
        ContentTypeService $contentTypeService
    ) {
        $this->expressionParser = $expressionParser;
        $this->contentTypeService = $contentTypeService;
    }

    public function extractFieldsFromExpression(string $expression): array
    {
        $extractedMetadata = $this->expressionParser->parseExpression($expression);

        $contentTypes = $this->resolveContentTypes($extractedMetadata);

        return $this->mergeFieldIds($extractedMetadata[2], $contentTypes);
    }

    public function isFieldWithinExpression(int $fieldDefinitionId, string $expression): bool
    {
        $fieldsFromExpression = $this->extractFieldsFromExpression($expression);

        return in_array($fieldDefinitionId, $fieldsFromExpression, true);
    }

    /**
     * @param array{non-empty-list<string>|null, non-empty-list<string>|null, non-empty-list<string>|null} $extractedMetadata
     *
     * @return list<ContentType>
     */
    private function resolveContentTypes(array $extractedMetadata): array
    {
        $contentTypeGroupIdentifiers = $extractedMetadata[0];
        $contentTypeIdentifiers = $extractedMetadata[1];

        // Resolve content type groups first
        if ($contentTypeGroupIdentifiers === null) {
            $contentTypeGroups = $this->contentTypeService->loadContentTypeGroups();
        } else {
            $contentTypeGroups = [];
            foreach ($contentTypeGroupIdentifiers as $contentTypeGroupIdentifier) {
                $contentTypeGroups[] = $this->contentTypeService->loadContentTypeGroupByIdentifier($contentTypeGroupIdentifier);
            }
        }

        $contentTypes = [];

        // Then resolve content types
        if ($contentTypeIdentifiers === null) {
            foreach ($contentTypeGroups as $contentTypeGroup) {
                $contentTypesInsideGroup = $this->contentTypeService->loadContentTypes($contentTypeGroup);
                foreach ($contentTypesInsideGroup as $contentType) {
                    $contentTypes[] = $contentType;
                }
            }
        } else {
            $contentTypes = array_map(
                [$this->contentTypeService, 'loadContentTypeByIdentifier'],
                $contentTypeIdentifiers,
            );

            $this->validateContentTypesInsideGroups($contentTypes, $contentTypeGroupIdentifiers);
        }

        return $contentTypes;
    }

    /**
     * @param array<int, string>|null $fieldIdentifiers
     *
     * @return array<int, int>
     */
    private function resolveFieldIds(?array $fieldIdentifiers, ContentType $contentType): array
    {
        $fieldDefinitions = $contentType->getFieldDefinitions();

        if ($fieldIdentifiers === null) {
            return $fieldDefinitions->map(
                static fn (FieldDefinition $fieldDefinition): int => $fieldDefinition->getId(),
            );
        }

        return $fieldDefinitions
            ->filter(
                static fn (FieldDefinition $fieldDefinition): bool => in_array($fieldDefinition->getIdentifier(), $fieldIdentifiers, true),
            )
            ->map(static fn (FieldDefinition $fieldDefinition): int => $fieldDefinition->getId());
    }

    /**
     * @param non-empty-list<ContentType> $contentTypes
     * @param list<string> $contentTypeGroupIdentifiers
     */
    private function validateContentTypesInsideGroups(
        array $contentTypes,
        ?array $contentTypeGroupIdentifiers
    ): void {
        if ($contentTypeGroupIdentifiers === null) {
            return;
        }

        foreach ($contentTypes as $contentType) {
            $groupsIdentifiers = array_map(
                static fn (ContentTypeGroup $group): string => $group->identifier,
                $contentType->getContentTypeGroups(),
            );

            if (array_intersect($contentTypeGroupIdentifiers, $groupsIdentifiers) === []) {
                throw new LogicException(
                    sprintf(
                        'Groups of content type "%s" have no common identifiers with chosen groups: "%s".',
                        $contentType->getIdentifier(),
                        implode(', ', $contentTypeGroupIdentifiers),
                    ),
                );
            }
        }
    }

    /**
     * @param list<string>|null $fieldIdentifiers
     * @param iterable<ContentType> $contentTypes
     *
     * @return list<int>
     */
    private function mergeFieldIds(?array $fieldIdentifiers, iterable $contentTypes): array
    {
        $finalFieldIds = [];
        foreach ($contentTypes as $contentType) {
            $finalFieldIds = array_merge(
                $finalFieldIds,
                $this->resolveFieldIds($fieldIdentifiers, $contentType),
            );
        }

        return $finalFieldIds;
    }
}
