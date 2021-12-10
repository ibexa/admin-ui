<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Input\Parser\ContentType;

use Ibexa\AdminUi\REST\Value\ContentType\FieldDefinitionCreate as FieldDefinitionCreateValue;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

final class FieldDefinitionCreate extends BaseParser
{
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): FieldDefinitionCreateValue
    {
        if (!array_key_exists('fieldTypeIdentifier', $data)) {
            throw new Exceptions\Parser(
                sprintf("Missing or invalid 'fieldTypeIdentifier' property for %s.", FieldDefinitionCreateValue::class)
            );
        }

        return new FieldDefinitionCreateValue(
            $data['fieldTypeIdentifier'],
            $data['fieldGroupIdentifier'] ?? null,
            $data['position'] ?? null
        );
    }
}
