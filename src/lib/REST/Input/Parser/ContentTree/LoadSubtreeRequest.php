<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Input\Parser\ContentTree;

use Ibexa\AdminUi\REST\Value\ContentTree\LoadSubtreeRequest as LoadSubtreeRequestValue;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Server\Input\Parser\Criterion as CriterionParser;

class LoadSubtreeRequest extends CriterionParser
{
    /**
     * @param array<mixed> $data
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidCriterionArgumentException
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): LoadSubtreeRequestValue
    {
        if (!array_key_exists('nodes', $data) || !is_array($data['nodes'])) {
            throw new Exceptions\Parser(
                sprintf("Missing or invalid 'nodes' property for %s.", self::class)
            );
        }

        $nodes = [];
        foreach ($data['nodes'] as $node) {
            $nodes[] = $parsingDispatcher->parse($node, $node['_media-type']);
        }

        $filter = null;
        if (array_key_exists('Filter', $data) && is_array($data['Filter'])) {
            $filter = $this->processCriteriaArray($data['Filter'], $parsingDispatcher);
        }

        return new LoadSubtreeRequestValue($nodes, $filter);
    }

    /**
     * @param array<string, mixed> $criteriaArray
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidCriterionArgumentException
     */
    private function processCriteriaArray(array $criteriaArray, ParsingDispatcher $parsingDispatcher): ?Criterion
    {
        if (count($criteriaArray) === 0) {
            return null;
        }

        $criteria = [];
        foreach ($criteriaArray as $criterionName => $criterionData) {
            $criteria[] = $this->dispatchCriterion($criterionName, $criterionData, $parsingDispatcher);
        }

        return (count($criteria) === 1) ? $criteria[0] : new Criterion\LogicalAnd($criteria);
    }
}

class_alias(LoadSubtreeRequest::class, 'EzSystems\EzPlatformAdminUi\REST\Input\Parser\ContentTree\LoadSubtreeRequest');
