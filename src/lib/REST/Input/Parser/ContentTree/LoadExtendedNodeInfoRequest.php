<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Input\Parser\ContentTree;

use Ibexa\AdminUi\REST\Value\ContentTree\LoadNodeExtendedInfoRequest as LoadNodeExtendedInfoRequestValue;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

class LoadExtendedNodeInfoRequest extends BaseParser
{
    /**
     * @param array<mixed> $data
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): LoadNodeExtendedInfoRequestValue
    {
        if (!array_key_exists('locationId', $data) || !is_int($data['locationId'])) {
            throw new Exceptions\Parser(
                sprintf("Missing or invalid 'locationId' property for %s.", self::class)
            );
        }

        $locationId = $data['locationId'];

        return new LoadNodeExtendedInfoRequestValue($locationId);
    }
}
