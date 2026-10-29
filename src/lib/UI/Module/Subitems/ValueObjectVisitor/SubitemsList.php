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

final class SubitemsList extends ValueObjectVisitor
{
    /**
     * @param \Ibexa\AdminUi\UI\Module\Subitems\Values\SubitemsList $data
     */
    public function visit(
        Visitor $visitor,
        Generator $generator,
        mixed $data
    ): void {
        $generator->startObjectElement('SubitemsList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('SubitemsList'));
        $visitor->suppressHeader('Accept-Patch');

        $generator->startList('SubitemsRow');
        foreach ($data->subitemRows as $subitemsRow) {
            $visitor->visitValueObject($subitemsRow);
        }
        $generator->endList('SubitemsRow');
        $generator->valueElement('ChildrenCount', $data->childrenCount);
        $generator->endObjectElement('SubitemsList');
    }
}
