<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Validator\Constraints;

use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\ContentForms\Validator\Constraints\FieldTypeValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Will check if validator configuration for FieldDefinition is valid.
 */
class ValidatorConfigurationValidator extends FieldTypeValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param \Ibexa\AdminUi\Form\Data\FieldDefinitionData $value The value that should be validated
     * @param \Symfony\Component\Validator\Constraint $constraint The constraint for the validation
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     *
     * @api
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof FieldDefinitionData) {
            return;
        }

        $fieldType = $this->fieldTypeService->getFieldType($value->getFieldTypeIdentifier());
        $this->processValidationErrors(
            iterator_to_array($fieldType->validateValidatorConfiguration($value->validatorConfiguration))
        );
    }

    protected function generatePropertyPath($errorIndex, $errorTarget): string
    {
        return 'defaultValue';
    }
}
