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
final class BulkOperationResponse extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\AdminUi\REST\Value\BulkOperationResponse $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startObjectElement('BulkOperationResponse');
        $visitor->setHeader('Content-Type', $generator->getMediaType('BulkOperationResponse'));
        $visitor->setStatus(Response::HTTP_OK);

        $generator->startHashElement('operations');

        foreach ($data->operations as $operationId => $operation) {
            $generator->startObjectElement($operationId, 'OperationResponse');
            $generator->valueElement('statusCode', $operation->statusCode);
            $generator->startHashElement('headers');

            foreach ($operation->headers as $name => $header) {
                $generator->valueElement($name, $header[0]);
            }
            $generator->endHashElement('headers');

            $generator->valueElement('content', $operation->content);

            $generator->endObjectElement($operationId);
        }
        $generator->endHashElement('operations');
        $generator->endObjectElement('BulkOperationResponse');
    }
}
