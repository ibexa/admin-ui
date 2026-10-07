<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Util;

use Ibexa\AdminUi\Exception\FieldTypeExpressionParserException;

interface ContentTypeFieldsExpressionParserInterface
{
    /**
     * @throws FieldTypeExpressionParserException
     */
    public function parseExpression(string $expression): ContentTypeFieldsParsedStructure;
}
