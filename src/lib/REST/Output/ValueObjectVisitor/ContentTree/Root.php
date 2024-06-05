<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Output\ValueObjectVisitor\ContentTree;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Symfony\Component\HttpFoundation\Response;

class Root extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Contracts\Rest\Output\Visitor $visitor
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param \Ibexa\AdminUi\REST\Value\ContentTree\Root $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ContentTreeRoot');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentTreeRoot'));
        $visitor->setStatus(Response::HTTP_OK);

        $generator->startList('ContentTreeNodeList');

        foreach ($data->elements as $element) {
            $visitor->visitValueObject($element);
        }

        $generator->endList('ContentTreeNodeList');

        $generator->endObjectElement('ContentTreeRoot');
    }
}
