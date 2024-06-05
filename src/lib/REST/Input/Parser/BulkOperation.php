<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Input\Parser;

use Ibexa\AdminUi\REST\Value\BulkOperation as BulkOperationValue;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

class BulkOperation extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \Ibexa\Contracts\Rest\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \Ibexa\AdminUi\REST\Value\BulkOperation
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!is_array($data) || 'operations' !== key($data)) {
            throw new Exceptions\Parser('Invalid structure for BulkOperation.');
        }

        if (array_key_exists('uri', $data['operations'])) {
            $operationData[] = $data['operations'];
        } else {
            $operationData = $data['operations'];
        }

        $operations = [];
        foreach ($operationData as $operationId => $operation) {
            $operations[$operationId] = $parsingDispatcher->parse($operation, 'application/vnd.ibexa.api.internal.Operation');
        }

        return new BulkOperationValue($operations);
    }
}
