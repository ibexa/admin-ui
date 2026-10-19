<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\ContentType;

use Ibexa\AdminUi\Exception\FieldTypeExpressionParserException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;

interface ContentTypeFieldsByExpressionServiceInterface
{
    /**
     * @return list<FieldDefinition>
     *
     * @throws NotFoundException
     * @throws FieldTypeExpressionParserException
     */
    public function getFieldsFromExpression(
        string $expression,
        ?string $configuration = null
    ): array;

    /**
     * @throws NotFoundException
     */
    public function isFieldIncludedInExpression(
        FieldDefinition $fieldDefinition,
        string $expression
    ): bool;
}
