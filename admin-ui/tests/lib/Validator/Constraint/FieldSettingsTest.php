<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Validator\Constraint;

use Ibexa\AdminUi\Validator\Constraints\FieldSettings;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class FieldSettingsTest extends TestCase
{
    public function testConstruct(): void
    {
        $constraint = new FieldSettings();
        self::assertSame('ez.field_definition.field_settings', $constraint->message);
    }

    public function testValidatedBy(): void
    {
        $constraint = new FieldSettings();
        self::assertSame('ezplatform.content_forms.validator.field_settings', $constraint->validatedBy());
    }

    public function testGetTargets(): void
    {
        $constraint = new FieldSettings();
        self::assertSame(Constraint::CLASS_CONSTRAINT, $constraint->getTargets());
    }
}
