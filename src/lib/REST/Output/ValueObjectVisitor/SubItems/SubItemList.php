<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Output\ValueObjectVisitor\SubItems;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Symfony\Component\HttpFoundation\Response;

final class SubItemList extends ValueObjectVisitor
{
    /**
     * @param \Ibexa\AdminUi\REST\Value\SubItems\SubItemList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startObjectElement('SubItems');
        $visitor->setHeader('Content-Type', $generator->getMediaType('SubItemList'));
        $visitor->setStatus(Response::HTTP_OK);

        $generator->valueElement('totalCount', $data->totalCount);

        $generator->startList('SubItemList');
        foreach ($data->elements as $element) {
            $visitor->visitValueObject($element);
        }

        $generator->endList('SubItemList');

        $generator->endObjectElement('SubItems');
    }
}
