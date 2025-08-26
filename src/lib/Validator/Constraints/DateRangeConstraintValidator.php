<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator\Constraints;

use DateTimeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class DateRangeConstraintValidator extends ConstraintValidator
{
    /**
     * @param \Ibexa\AdminUi\Form\Data\DateRangeData|null $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof DateRangeConstraint) {
            throw new UnexpectedTypeException($constraint, DateRangeConstraint::class);
        }

        if ($value === null) {
            return;
        }

        $min = $value->getMin();
        $max = $value->getMax();

        if ($min instanceof DateTimeInterface && $max instanceof DateTimeInterface && $min > $max) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('min')
                ->addViolation();

            $this->context
                ->buildViolation($constraint->message)
                ->atPath('max')
                ->addViolation();
        }
    }
}
