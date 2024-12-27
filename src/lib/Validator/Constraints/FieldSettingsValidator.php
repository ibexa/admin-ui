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
 * Will check if field settings for FieldDefinition are valid.
 */
class FieldSettingsValidator extends FieldTypeValidator
{
    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof FieldDefinitionData) {
            return;
        }

        $fieldType = $this->fieldTypeService->getFieldType($value->getFieldTypeIdentifier());
        $this->processValidationErrors(iterator_to_array($fieldType->validateFieldSettings($value->fieldSettings)));
    }

    protected function generatePropertyPath($errorIndex, $errorTarget): string
    {
        return 'fieldSettings' . $errorTarget;
    }
}
