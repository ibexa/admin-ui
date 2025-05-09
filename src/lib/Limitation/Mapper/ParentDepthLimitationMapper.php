<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\LimitationValueMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Core\Limitation\LimitationIdentifierToLabelConverter;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

class ParentDepthLimitationMapper extends MultipleSelectionBasedMapper implements LimitationValueMapperInterface, TranslationContainerInterface
{
    /**
     * @var int The maximum possible depth to use in a limitation
     */
    private $maxDepth;

    public function __construct($maxDepth)
    {
        $this->maxDepth = $maxDepth;
    }

    /**
     * @return mixed[]
     */
    protected function getSelectionChoices(): array
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

    public static function getTranslationMessages(): array
    {
        return [
            Message::create(
                LimitationIdentifierToLabelConverter::convert('parentdepth'),
                'ibexa_content_forms_policies'
            )->setDesc('Parent Depth'),
        ];
    }
}
