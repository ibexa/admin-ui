<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Input\Parser\ContentType;

use Ibexa\AdminUi\REST\Value\ContentType\FieldDefinitionReorder as FieldDefinitionReorderValue;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

final class FieldDefinitionReorder extends BaseParser
{
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): FieldDefinitionReorderValue
    {
        if (!array_key_exists('fieldDefinitionIdentifiers', $data)) {
            throw new Exceptions\Parser(
                sprintf("Missing or invalid 'fieldDefinitionIdentifiers' property for %s.", FieldDefinitionReorderValue::class)
            );
        }

        return new FieldDefinitionReorderValue($data['fieldDefinitionIdentifiers']);
    }
}
