<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Symfony\Component\HttpFoundation\Response;

/**
 * BulkOperationResponse value object visitor.
 */
class BulkOperationResponse extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Contracts\Rest\Output\Visitor $visitor
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param \Ibexa\AdminUi\REST\Value\BulkOperationResponse $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('BulkOperationResponse');
        $visitor->setHeader('Content-Type', $generator->getMediaType('BulkOperationResponse'));
        $visitor->setStatus(Response::HTTP_OK);

        $generator->startHashElement('operations');

        foreach ($data->operations as $operationId => $operation) {
            $generator->startObjectElement($operationId, 'OperationResponse');

            $generator->startValueElement('statusCode', $operation->statusCode);
            $generator->endValueElement('statusCode');

            $generator->startHashElement('headers');

            foreach ($operation->headers as $name => $header) {
                $generator->startValueElement($name, $header[0]);
                $generator->endValueElement($name);
            }
            $generator->endHashElement('headers');

            $generator->startValueElement('content', $operation->content);
            $generator->endValueElement('content');

            $generator->endObjectElement($operationId);
        }
        $generator->endHashElement('operations');
        $generator->endObjectElement('BulkOperationResponse');
    }
}
