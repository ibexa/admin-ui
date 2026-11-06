<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\BulkOperation;

use Ibexa\AdminUi\REST\Value\BulkOperation;
use Ibexa\AdminUi\REST\Value\BulkOperationResponse;
use Ibexa\AdminUi\REST\Value\Operation;
use Ibexa\AdminUi\REST\Value\OperationResponse;
use Ibexa\Contracts\Rest\Exceptions\InvalidArgumentException;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class BulkOperationController extends RestController
{
    public function __construct(
        private readonly HttpKernelInterface $httpKernel
    ) {}

    /**
     * @throws \Exception
     */
    public function bulkAction(Request $request): BulkOperationResponse
    {
        /** @var BulkOperation $operationList */
        $operationList = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $responses = [];
        foreach ($operationList->operations as $operationId => $operation) {
            $response = $this->httpKernel->handle(
                $this->buildSubRequest($request, $operation),
                HttpKernelInterface::SUB_REQUEST
            );

            $responses[$operationId] = new OperationResponse(
                $response->getStatusCode(),
                $response->headers->all(),
                $response->getContent() ?: '',
            );
        }

        return new BulkOperationResponse($responses);
    }

    /**
     * @throws BadRequestException
     */
    private function buildSubRequest(
        Request $request,
        Operation $operation
    ): Request {
        $subRequest = Request::create(
            $operation->uri,
            $operation->method,
            $operation->parameters,
            [],
            [],
            [
                'HTTP_X-CSRF-Token' => $request->headers->get('X-CSRF-Token'),
                'HTTP_SiteAccess' => $request->headers->get('SiteAccess'),
            ],
            $operation->content
        );
        foreach ($operation->headers as $name => $value) {
            $subRequest->headers->set($name, $value);
        }

        // Sub-requests bypass checks applied to main requests (e.g. fragment URI signing),
        // so only existing REST routes can be targeted
        try {
            $this->uriParser->matchUri($subRequest->getPathInfo(), $subRequest->getMethod());
        } catch (InvalidArgumentException $e) {
            throw new BadRequestException('Bulk operation URI must point to a REST resource.', 0, $e);
        }

        $subRequest->setSession($request->getSession());

        return $subRequest;
    }
}
