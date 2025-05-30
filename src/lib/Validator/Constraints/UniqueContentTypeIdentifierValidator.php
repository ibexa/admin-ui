<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Validator\Constraints;

use Ibexa\AdminUi\Form\Data\ContentTypeData;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Will check if ContentType identifier is not already used in the content repository.
 */
class UniqueContentTypeIdentifierValidator extends ConstraintValidator
{
    private ContentTypeService $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param \Ibexa\AdminUi\Form\Data\ContentTypeData $value The value that should be validated
     * @param \Symfony\Component\Validator\Constraint|UniqueFieldDefinitionIdentifier $constraint The constraint for the validation
     *
     * @api
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof ContentTypeData || $value->identifier === null) {
            return;
        }

        try {
            $contentType = $this->contentTypeService->loadContentTypeByIdentifier($value->identifier);
            // It's of course OK to edit a draft of an existing ContentType :-)
            if ($contentType->id === $value->contentTypeDraft->id) {
                return;
            }

            $this->context->buildViolation($constraint->message)
                ->atPath('identifier')
                ->setParameter('%identifier%', $value->identifier)
                ->addViolation();
        } catch (NotFoundException $e) {
            // Do nothing
        }
    }
}
