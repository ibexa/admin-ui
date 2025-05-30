<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator\Constraints;

use Ibexa\AdminUi\Specification\Location\IsContainer;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LocationIsContainerValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location The value that should be validated
     * @param \Symfony\Component\Validator\Constraint $constraint The constraint for the validation
     */
    public function validate($location, Constraint $constraint): void
    {
        if (null === $location) {
            $this->context->addViolation($constraint->message);

            return;
        }

        $isContainer = new IsContainer();
        if (!$isContainer->isSatisfiedBy($location)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
