<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Validator\Constraints;

use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\ContentForms\Validator\Constraints\FieldTypeValidator;
use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Symfony\Component\Validator\Constraint;

/**
 * Validator for default value from FieldDefinitionData.
 */
class FieldDefinitionDefaultValueValidator extends FieldTypeValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof FieldDefinitionData) {
            return;
        }

        $fieldValue = $this->getFieldValue($value);
        if (!$fieldValue) {
            return;
        }

        $fieldTypeIdentifier = $this->getFieldTypeIdentifier($value);
        $fieldDefinition = $this->getFieldDefinition($value);
        $fieldType = $this->fieldTypeService->getFieldType($fieldTypeIdentifier);

        $validationErrors = $fieldType->validateValue($fieldDefinition, $fieldValue);

        $this->processValidationErrors(iterator_to_array($validationErrors));
    }

    protected function getFieldValue(FieldDefinitionData $value): ?Value
    {
        return $value->defaultValue;
    }

    /**
     * Returns the field definition $value refers to.
     * FieldDefinition object is needed to validate field value against field settings.
     */
    protected function getFieldDefinition(FieldDefinitionData $value): FieldDefinition
    {
        return $value->fieldDefinition;
    }

    /**
     * Returns the fieldTypeIdentifier for the field value to validate.
     */
    protected function getFieldTypeIdentifier(FieldDefinitionData $value): string
    {
        return $value->getFieldTypeIdentifier();
    }

    protected function generatePropertyPath($errorIndex, $errorTarget): string
    {
        return 'defaultValue';
    }
}
