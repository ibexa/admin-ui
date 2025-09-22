<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\ContentType;

use Ibexa\AdminUi\Util\ContentTypeFieldsExtractorInterface;
use Ibexa\Contracts\AdminUi\ContentType\ContentTypeFieldsByExpressionServiceInterface;
use Ibexa\Contracts\Core\Persistence\Content\Type\Handler as ContentTypeHandler;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Mapper\ContentTypeDomainMapper;

final class ContentTypeFieldsByExpressionService implements ContentTypeFieldsByExpressionServiceInterface
{
    private ContentTypeFieldsExtractorInterface $fieldsExtractor;

    private ContentTypeHandler $contentTypeHandler;

    private ContentTypeDomainMapper $contentTypeDomainMapper;

    public function __construct(
        ContentTypeFieldsExtractorInterface $fieldsExtractor,
        ContentTypeHandler $contentTypeHandler,
        ContentTypeDomainMapper $contentTypeDomainMapper
    ) {
        $this->fieldsExtractor = $fieldsExtractor;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->contentTypeDomainMapper = $contentTypeDomainMapper;
    }

    public function getFieldsFromExpression(string $expression): array
    {
        $contentTypeFieldIds = $this->fieldsExtractor->extractFieldsFromExpression($expression);

        $contentTypeFieldDefinitions = [];
        foreach ($contentTypeFieldIds as $contentTypeFieldId) {
            $persistenceFieldDefinition = $this->contentTypeHandler->getFieldDefinition(
                $contentTypeFieldId,
                ContentType::STATUS_DEFINED,
            );
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
