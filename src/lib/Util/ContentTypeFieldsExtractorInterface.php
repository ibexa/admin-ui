<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Util;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;

interface ContentTypeFieldsExtractorInterface
{
    /**
     * @return list<int>
     *
     * @throws NotFoundException
     */
    public function extractFieldsFromExpression(string $expression): array;

    /**
     * @throws NotFoundException
     */
    public function isFieldWithinExpression(
        int $fieldDefinitionId,
        string $expression
    ): bool;
}
