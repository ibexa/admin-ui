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
        if (!array_key_exists('Location', $data) || !is_array($data['Location'])) {
            throw new Exceptions\Parser(
                sprintf("Missing or invalid 'Location' element for %s.", self::class)
            );
        }

        if (!array_key_exists('_href', $data['Location'])) {
            throw new Exceptions\Parser("Missing '_href' attribute for the Location element in LoadExtendedNodeInfoRequest.");
        }

        $locationHrefParts = explode(
            '/',
            $this->requestParser->parseHref($data['Location']['_href'], 'locationPath')
        );
        $locationId = (int)array_pop($locationHrefParts);

        return new LoadNodeExtendedInfoRequestValue($locationId);
    }
}
