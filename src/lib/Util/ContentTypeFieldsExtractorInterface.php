<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Util;

interface ContentTypeFieldsExtractorInterface
{
    /**
     * @return list<int>
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\AdminUi\Exception\FieldTypeExpressionParserException
     * @throws \LogicException
     */
    public function extractFieldsFromExpression(string $expression): array;

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function isFieldWithinExpression(int $fieldDefinitionId, string $expression): bool;
}
