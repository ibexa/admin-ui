<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Module\Subitems\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;

class SubitemsList extends ValueObjectVisitor
{
    /**
     * @param \Ibexa\Contracts\Rest\Output\Visitor $visitor
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param \Ibexa\AdminUi\UI\Module\Subitems\Values\SubitemsList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('SubitemsList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('SubitemsList'));
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader('Accept-Patch', false);

        $generator->startList('SubitemsRow');
        foreach ($data->subitemRows as $subitemsRow) {
            $visitor->visitValueObject($subitemsRow);
        }
        $generator->endList('SubitemsRow');

        $generator->startValueElement('ChildrenCount', $data->childrenCount);
        $generator->endValueElement('ChildrenCount');

        $generator->endObjectElement('SubitemsList');
    }
}
