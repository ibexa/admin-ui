<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;

class ParentDepthLimitationMapper extends MultipleSelectionBasedMapper implements LimitationValueMapperInterface
{
    /**
     * @var int The maximum possible depth to use in a limitation
     */
    private $maxDepth;

    public function __construct($maxDepth)
    {
        $this->maxDepth = $maxDepth;
    }

    protected function getSelectionChoices()
    {
        $choices = [];
        foreach (range(1, $this->maxDepth) as $depth) {
            $choices[$depth] = $depth;
        }

        return $choices;
    }

    public function mapLimitationValue(Limitation $limitation)
    {
        return $limitation->limitationValues;
    }
}

class_alias(ParentDepthLimitationMapper::class, 'EzSystems\EzPlatformAdminUi\Limitation\Mapper\ParentDepthLimitationMapper');
