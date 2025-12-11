<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator\Constraints;

use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Will check if FieldDefinition identifier is not already used within ContentType.
 */
final class UniqueFieldDefinitionIdentifierValidator extends ConstraintValidator
{
    /**
     * @param \Symfony\Component\Validator\Constraint|UniqueFieldDefinitionIdentifier $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof FieldDefinitionData) {
            return;
        }

        $contentTypeData = $value->contentTypeData;
        foreach ($contentTypeData->getFlatFieldDefinitionsData() as $fieldDefData) {
            if ($fieldDefData === $value) {
                continue;
            }

            if ($value->identifier === $fieldDefData->identifier) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('identifier')
                    ->setParameter('%identifier%', $value->identifier)
                    ->addViolation();
            }
        }
    }
}
