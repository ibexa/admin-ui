<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\ContentType;

use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;

interface ContentTypeFieldsByExpressionServiceInterface
{
    /**
     * @return list<\Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition>
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function getFieldsFromExpression(string $expression): array;

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function isFieldIncludedInExpression(FieldDefinition $fieldDefinition, string $expression): bool;
}
