<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Input\Parser\ContentType;

use Ibexa\AdminUi\REST\Value\ContentType\FieldDefinitionExpression as FieldDefinitionExpressionValue;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

final class FieldDefinitionExpression extends BaseParser
{
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): FieldDefinitionExpressionValue
    {
        if (!array_key_exists('expression', $data) || !is_string($data['expression'])) {
            throw new Exceptions\Parser(
                sprintf("Missing or invalid 'expression' property for %s.", FieldDefinitionExpressionValue::class)
            );
        }

        return new FieldDefinitionExpressionValue($data['expression']);
    }
}
