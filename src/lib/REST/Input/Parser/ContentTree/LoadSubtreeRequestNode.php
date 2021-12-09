<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Input\Parser\ContentTree;

use Ibexa\AdminUi\REST\Value\ContentTree\LoadSubtreeRequestNode as LoadSubtreeRequestNodeValue;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

class LoadSubtreeRequestNode extends BaseParser
{
    /**
     * {@inheritdoc}
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): LoadSubtreeRequestNodeValue
    {
        if (!array_key_exists('locationId', $data) || !is_numeric($data['locationId'])) {
            throw new Exceptions\Parser(sprintf("Missing or invalid 'locationId' property for %s.", self::class));
        }

        if (!array_key_exists('limit', $data) || !is_numeric($data['limit'])) {
            throw new Exceptions\Parser(sprintf(
                "Missing or invalid 'limit' property for %s.",
                self::class
            ));
        }

        if (!array_key_exists('offset', $data) || !is_numeric($data['offset'])) {
            throw new Exceptions\Parser(sprintf(
                "Missing or invalid 'offset' property for %s.",
                self::class
            ));
        }

        if (!array_key_exists('children', $data) || !is_array($data['children'])) {
            throw new Exceptions\Parser(sprintf(
                "Missing or invalid 'children' property for %s.",
                self::class
            ));
        }

        $children = [];
        foreach ($data['children'] as $child) {
            $children[] = $parsingDispatcher->parse($child, $child['_media-type']);
        }

        return new LoadSubtreeRequestNodeValue(
            (int) $data['locationId'],
            (int) $data['limit'],
            (int) $data['offset'],
            $children
        );
    }
}

class_alias(LoadSubtreeRequestNode::class, 'EzSystems\EzPlatformAdminUi\REST\Input\Parser\ContentTree\LoadSubtreeRequestNode');
