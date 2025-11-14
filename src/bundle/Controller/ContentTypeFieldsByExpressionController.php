<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Exception\FieldTypeExpressionParserException;
use Ibexa\AdminUi\REST\Value\ContentType\FieldDefinitionInfoList;
use Ibexa\Contracts\AdminUi\ContentType\ContentTypeFieldsByExpressionServiceInterface;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use LogicException;
use Symfony\Component\HttpFoundation\Request;

final class ContentTypeFieldsByExpressionController extends RestController
{
    private ContentTypeFieldsByExpressionServiceInterface $fieldsByExpressionService;

    public function __construct(ContentTypeFieldsByExpressionServiceInterface $fieldsByExpressionService)
    {
        $this->fieldsByExpressionService = $fieldsByExpressionService;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function loadFieldDefinitionsFromExpression(Request $request): FieldDefinitionInfoList
    {
        /** @var \Ibexa\AdminUi\REST\Value\ContentType\FieldDefinitionExpression $input */
        $input = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            $fieldDefinitions = $this->fieldsByExpressionService->getFieldsFromExpression(
                $input->expression,
                $input->configuration,
            );
        } catch (FieldTypeExpressionParserException | LogicException $e) {
            throw new BadRequestException($e->getMessage(), $e->getCode(), $e);
        }

        return new FieldDefinitionInfoList($fieldDefinitions);
    }
}
