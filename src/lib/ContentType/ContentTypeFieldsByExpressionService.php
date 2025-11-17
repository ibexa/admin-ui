<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\ContentType;

use Ibexa\AdminUi\Util\ContentTypeFieldsExtractorInterface;
use Ibexa\Contracts\AdminUi\ContentType\ContentTypeFieldsByExpressionServiceInterface;
use Ibexa\Contracts\Core\Persistence\Content\Language\Handler as ContentLanguageHandler;
use Ibexa\Contracts\Core\Persistence\Content\Type\Handler as ContentTypeHandler;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\FieldType\FieldTypeRegistry;
use Ibexa\Core\Repository\Mapper\ContentTypeDomainMapper;

final class ContentTypeFieldsByExpressionService implements ContentTypeFieldsByExpressionServiceInterface
{
    private ContentTypeFieldsExtractorInterface $fieldsExtractor;

    private ContentTypeHandler $contentTypeHandler;

    private ContentTypeDomainMapper $contentTypeDomainMapper;

    private ConfigResolverInterface $configResolver;

    public function __construct(
        ContentTypeFieldsExtractorInterface $fieldsExtractor,
        ContentTypeHandler $contentTypeHandler,
        ContentLanguageHandler $contentLanguageHandler,
        FieldTypeRegistry $fieldTypeRegistry,
        ConfigResolverInterface $configResolver
    ) {
        $this->fieldsExtractor = $fieldsExtractor;
        $this->contentTypeHandler = $contentTypeHandler;
        // Building ContentTypeDomainMapper manually to avoid circular dependency.
        //TODO handle after core merge
        $this->contentTypeDomainMapper = new ContentTypeDomainMapper(
            $contentTypeHandler,
            $contentLanguageHandler,
            $fieldTypeRegistry,
        );
        $this->configResolver = $configResolver;
    }

    public function getFieldsFromExpression(string $expression, ?string $configuration = null): array
    {
        $contentTypeFieldIds = $this->fieldsExtractor->extractFieldsFromExpression($expression);

        $configuration = $configuration !== null
            ? $this->configResolver->getParameter("content_type_field_type_groups.configurations.$configuration")
            : null;

        $contentTypeFieldDefinitions = [];
        foreach ($contentTypeFieldIds as $contentTypeFieldId) {
            $persistenceFieldDefinition = $this->contentTypeHandler->getFieldDefinition(
                $contentTypeFieldId,
                ContentType::STATUS_DEFINED,
            );

            if (
                $configuration !== null
                && !in_array($persistenceFieldDefinition->fieldType, $configuration, true)
            ) {
                continue;
            }

            $apiFieldDefinition = $this->contentTypeDomainMapper->buildFieldDefinitionDomainObject(
                $persistenceFieldDefinition,
                $persistenceFieldDefinition->mainLanguageCode,
            );

            $contentTypeFieldDefinitions[] = $apiFieldDefinition;
        }

        return $contentTypeFieldDefinitions;
    }

    public function isFieldIncludedInExpression(FieldDefinition $fieldDefinition, string $expression): bool
    {
        return $this->fieldsExtractor->isFieldWithinExpression($fieldDefinition->getId(), $expression);
    }
}
