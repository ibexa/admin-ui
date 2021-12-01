<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\BulkOperation;

use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\AdminUi\REST\Value\BulkOperationResponse;
use Ibexa\AdminUi\REST\Value\Operation;
use Ibexa\AdminUi\REST\Value\OperationResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class BulkOperationController extends RestController
{
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface */
    private $httpKernel;

    /**
     * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
     */
    public function __construct(
        HttpKernelInterface $httpKernel
    ) {
        $this->httpKernel = $httpKernel;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Ibexa\AdminUi\REST\Value\BulkOperationResponse
     *
     * @throws \Exception
     */
    public function bulkAction(Request $request): BulkOperationResponse
    {
        /** @var \Ibexa\AdminUi\REST\Value\BulkOperation $operationList */
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
                $response->getContent()
            );
        }

        return new BulkOperationResponse($responses);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\AdminUi\REST\Value\Operation $operation
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    private function buildSubRequest(Request $request, Operation $operation): Request
    {
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
        $subRequest->setSession($request->getSession());
        foreach ($operation->headers as $name => $value) {
            $subRequest->headers->set($name, $value);
        }

        return $subRequest;
    }
}

class_alias(BulkOperationController::class, 'EzSystems\EzPlatformAdminUiBundle\Controller\BulkOperation\BulkOperationController');
