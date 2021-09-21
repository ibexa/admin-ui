<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Input\Parser;

use EzSystems\EzPlatformRest\Exceptions;
use EzSystems\EzPlatformRest\Input\BaseParser;
use EzSystems\EzPlatformRest\Input\ParsingDispatcher;
use Ibexa\AdminUi\REST\Value\Operation as OperationValue;

class Operation extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \EzSystems\EzPlatformRest\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \EzSystems\EzPlatformAdminUi\REST\Value\Operation
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('uri', $data) || !is_string($data['uri'])) {
            throw new Exceptions\Parser("Missing or invalid 'uri' element for BulkOperation.");
        }

        if (!array_key_exists('method', $data) || !is_string($data['method'])) {
            throw new Exceptions\Parser("Missing or invalid 'method' element for BulkOperation.");
        }

        if (array_key_exists('headers', $data) && !is_array($data['headers'])) {
            throw new Exceptions\Parser("Missing or invalid 'headers' element for BulkOperation.");
        }

        if (array_key_exists('parameters', $data) && !is_array($data['parameters'])) {
            throw new Exceptions\Parser("Missing or invalid 'parameters' element for BulkOperation.");
        }

        if (array_key_exists('content', $data) && !is_string($data['content'])) {
            throw new Exceptions\Parser("Missing or invalid 'content' element for BulkOperation.");
        }

        $operation = new OperationValue(
            $data['uri'],
            $data['method'],
            $data['parameters'] ?? [],
            $data['headers'] ?? [],
            $data['content'] ?? ''
        );

        return $operation;
    }
}

class_alias(Operation::class, 'EzSystems\EzPlatformAdminUi\REST\Input\Parser\Operation');
