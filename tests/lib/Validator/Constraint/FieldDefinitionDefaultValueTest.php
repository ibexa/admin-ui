<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Validator\Constraint;

use Ibexa\AdminUi\Validator\Constraints\FieldDefinitionDefaultValue;
use Ibexa\AdminUi\Validator\Constraints\FieldDefinitionDefaultValueValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class FieldDefinitionDefaultValueTest extends TestCase
{
    public function testConstruct(): void
    {
        $constraint = new FieldDefinitionDefaultValue();
        self::assertSame('ez.field_definition.default_field_value', $constraint->message);
    }

    public function testValidatedBy(): void
    {
        $constraint = new FieldDefinitionDefaultValue();
        self::assertSame(FieldDefinitionDefaultValueValidator::class, $constraint->validatedBy());
    }

    public function testGetTargets(): void
    {
        $constraint = new FieldDefinitionDefaultValue();
        self::assertSame(Constraint::CLASS_CONSTRAINT, $constraint->getTargets());
    }
}
