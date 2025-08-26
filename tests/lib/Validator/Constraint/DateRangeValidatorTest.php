<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Validator\Constraint;

use DateTimeImmutable;
use Ibexa\AdminUi\Form\Data\DateRangeData;
use Ibexa\AdminUi\Validator\Constraints\DateRangeConstraint;
use Ibexa\AdminUi\Validator\Constraints\DateRangeConstraintValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class DateRangeValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): DateRangeConstraintValidator
    {
        return new DateRangeConstraintValidator();
    }

    public function testValidRange(): void
    {
        $data = new DateRangeData(
            new DateTimeImmutable('2024-01-01 00:00:00'),
            new DateTimeImmutable('2024-01-02 00:00:00'),
        );

        $this->validator->validate($data, new DateRangeConstraint());

        $this->assertNoViolation();
    }

    public function testInvalidRange(): void
    {
        $data = new DateRangeData(
            new DateTimeImmutable('2024-01-05 00:00:00'),
            new DateTimeImmutable('2024-01-01 00:00:00'),
        );

        $constraint = new DateRangeConstraint([
            'message' => 'ibexa.date_range.invalid_range',
        ]);

        $this->validator->validate($data, $constraint);

        $this
            ->buildViolation('ibexa.date_range.invalid_range')
            ->atPath('property.path.min')
            ->buildNextViolation('ibexa.date_range.invalid_range')
            ->atPath('property.path.max')
            ->assertRaised();
    }

    public function testNullValuesAreValid(): void
    {
        $data = new DateRangeData(null, null);

        $this->validator->validate($data, new DateRangeConstraint());

        $this->assertNoViolation();
    }
}
