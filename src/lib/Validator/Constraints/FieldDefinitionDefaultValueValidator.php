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
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition as CoreFieldDefinition;
use Symfony\Component\Validator\Constraint;

/**
 * Validator for default value from FieldDefinitionData.
 */
class FieldDefinitionDefaultValueValidator extends FieldTypeValidator
{
    public function validate(
        $value,
        Constraint $constraint
    ) {
        if (!$value instanceof FieldDefinitionData) {
            return;
        }

        $fieldValue = $this->getFieldValue($value);
        if (!$fieldValue) {
            return;
        }

        // The stored default value is kept when the form has no default value field (e.g. Selection),
        // so it can't be changed in the editor and is not validated against the submitted settings.
        if ($fieldValue === $value->fieldDefinition->getDefaultValue()) {
            return;
        }

        $fieldTypeIdentifier = $this->getFieldTypeIdentifier($value);
        $fieldDefinition = $this->getFieldDefinition($value);
        $fieldType = $this->fieldTypeService->getFieldType($fieldTypeIdentifier);

        $validationErrors = $fieldType->validateValue($fieldDefinition, $fieldValue);

        $this->processValidationErrors($validationErrors);
    }

    protected function getFieldValue(FieldDefinitionData $value): ?Value
    {
        return $value->defaultValue;
    }

    /**
     * Returns the field definition $value refers to.
     * FieldDefinition object is needed to validate field value against field settings.
     *
     * Field settings and validator configuration are taken from the submitted data, not from the stored
     * field definition, as they are persisted together with the default value.
     */
    protected function getFieldDefinition(FieldDefinitionData $value): FieldDefinition
    {
        $fieldDefinition = $value->fieldDefinition;

        return new CoreFieldDefinition([
            'id' => $fieldDefinition->getId(),
            'identifier' => $fieldDefinition->getIdentifier(),
            'fieldGroup' => $fieldDefinition->getFieldGroup(),
            'position' => $fieldDefinition->getPosition(),
            'fieldTypeIdentifier' => $fieldDefinition->getFieldTypeIdentifier(),
            'isTranslatable' => $fieldDefinition->isTranslatable(),
            'isThumbnail' => $fieldDefinition->isThumbnail(),
            'isRequired' => $fieldDefinition->isRequired(),
            'isInfoCollector' => $fieldDefinition->isInfoCollector(),
            'isSearchable' => $fieldDefinition->isSearchable(),
            'defaultValue' => $fieldDefinition->getDefaultValue(),
            'mainLanguageCode' => $fieldDefinition->getMainLanguageCode(),
            // No getter for prioritized languages, read through ValueObject::__get()
            // @phpstan-ignore-next-line
            'prioritizedLanguages' => $fieldDefinition->prioritizedLanguages,
            'names' => $fieldDefinition->getNames(),
            'descriptions' => $fieldDefinition->getDescriptions(),
            'fieldSettings' => $value->fieldSettings ?? $fieldDefinition->getFieldSettings(),
            'validatorConfiguration' => $value->validatorConfiguration ?? $fieldDefinition->getValidatorConfiguration(),
        ]);
    }

    /**
     * Returns the fieldTypeIdentifier for the field value to validate.
     */
    protected function getFieldTypeIdentifier(FieldDefinitionData $value): string
    {
        return $value->getFieldTypeIdentifier();
    }

    protected function generatePropertyPath(
        $errorIndex,
        $errorTarget
    ): string {
        return 'defaultValue';
    }
}

class_alias(FieldDefinitionDefaultValueValidator::class, 'EzSystems\EzPlatformAdminUi\Validator\Constraints\FieldDefinitionDefaultValueValidator');
