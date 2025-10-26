<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator\Constraints;

use Ibexa\AdminUi\Specification\Location\IsContainer;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LocationIsContainerValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param Location $location The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate(
        $location,
        Constraint $constraint
    ) {
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

class_alias(LocationIsContainerValidator::class, 'EzSystems\EzPlatformAdminUi\Validator\Constraints\LocationIsContainerValidator');
