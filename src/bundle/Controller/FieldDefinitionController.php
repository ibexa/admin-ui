<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Exception;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class FieldDefinitionController extends RestController
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(ContentTypeService $contentTypeService, UrlGeneratorInterface $urlGenerator)
    {
        $this->contentTypeService = $contentTypeService;
        $this->urlGenerator = $urlGenerator;
    }

    public function addFieldDefinitionAction(
        Request $request,
        ContentTypeGroup $group,
        ContentTypeDraft $contentTypeDraft,
        Language $language,
        ?Language $baseLanguage = null
    ): RedirectResponse {
        /** @var \Ibexa\AdminUi\REST\Value\ContentType\FieldDefinitionCreate $input */
        $input = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $fieldDefinitionCreateStruct = $this->contentTypeService->newFieldDefinitionCreateStruct(
            uniqid('field_'),
            $input->fieldTypeIdentifier
        );

        $fieldDefinitionCreateStruct->fieldGroup = $input->fieldGroupIdentifier;
        $fieldDefinitionCreateStruct->names = [
            $language->languageCode => 'New field type',
        ];

        $fieldDefinitionCreateStruct->position = $input->position ?? $this->getNextFieldPosition($contentTypeDraft);

        $this->contentTypeService->addFieldDefinition(
            $contentTypeDraft,
            $fieldDefinitionCreateStruct
        );

        return new RedirectResponse(
            $this->urlGenerator->generate(
                'ibexa.content_type.field_definition_form',
                [
                    'fieldDefinitionIdentifier' => $fieldDefinitionCreateStruct->identifier,
                    'contentTypeGroupId' => $group->id,
                    'contentTypeId' => $contentTypeDraft->id,
                    'toLanguageCode' => $language->languageCode,
                    'fromLanguageCode' => $baseLanguage ? $baseLanguage->languageCode : null,
                ]
            )
        );
    }

    public function removeFieldDefinitionAction(
        Request $request,
        ContentTypeGroup $group,
        ContentTypeDraft $contentTypeDraft
    ): Values\OK {
        /** @var \Ibexa\AdminUi\REST\Value\ContentType\FieldDefinitionDelete $input */
        $input = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $this->repository->beginTransaction();
        try {
            foreach ($input->fieldDefinitionIdentifiers as $identifier) {
                if (!$contentTypeDraft->fieldDefinitions->has($identifier)) {
                    throw new Exceptions\NotFoundException("No field definition with $identifier found");
                }

                $this->contentTypeService->removeFieldDefinition(
                    $contentTypeDraft,
                    $contentTypeDraft->fieldDefinitions->get($identifier)
                );
            }

            $this->repository->commit();
        } catch (InvalidArgumentException $e) {
            $this->repository->rollback();

            throw new Exceptions\ForbiddenException($e->getMessage());
        } catch (Exception $e) {
            $this->repository->rollback();

            throw $e;
        }

        return new Values\OK();
    }

    public function reorderFieldDefinitionsAction(
        Request $request,
        ContentTypeGroup $group,
        ContentTypeDraft $contentTypeDraft
    ): Values\OK {
        /** @var \Ibexa\AdminUi\REST\Value\ContentType\FieldDefinitionReorder $input */
        $input = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $this->repository->beginTransaction();
        try {
            foreach ($input->fieldDefinitionIdentifiers as $position => $identifier) {
                $updateStruct = $this->contentTypeService->newFieldDefinitionUpdateStruct();
                $updateStruct->position = $position;

                $this->contentTypeService->updateFieldDefinition(
                    $contentTypeDraft,
                    $contentTypeDraft->getFieldDefinition($identifier),
                    $updateStruct
                );
            }

            $this->repository->commit();
        } catch (InvalidArgumentException $e) {
            $this->repository->rollback();

            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        return new Values\OK();
    }

    private function getNextFieldPosition(ContentType $contentType): int
    {
        if (!$contentType->fieldDefinitions->isEmpty()) {
            return $contentType->fieldDefinitions->last()->position + 1;
        }

        return 0;
    }
}
