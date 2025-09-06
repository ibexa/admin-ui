<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator\Constraints;

use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\ContentForms\Validator\Constraints\FieldTypeValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Will check if validator configuration for FieldDefinition is valid.
 */
final class ValidatorConfigurationValidator extends FieldTypeValidator
{
    /**
     * Checks if the passed value is valid.
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
            iterator_to_array(
                $fieldType->validateValidatorConfiguration($value->validatorConfiguration)
            )
        );
    }

    protected function generatePropertyPath(int $errorIndex, ?string $errorTarget): string
    {
        return 'defaultValue';
    }
}
