<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator\Constraints;

use Ibexa\AdminUi\Specification\Location\HasChildren;
use Ibexa\Contracts\Core\Repository\LocationService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class LocationHasChildrenValidator extends ConstraintValidator
{
    public function __construct(private readonly LocationService $locationService)
    {
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param \Symfony\Component\Validator\Constraint $constraint The constraint for the validation
     */
    public function validate(mixed $location, Constraint $constraint): void
    {
        if (null === $location) {
            $this->context->addViolation($constraint->message);

            return;
        }

        $hasChildren = new HasChildren($this->locationService);

        if (!$hasChildren->isSatisfiedBy($location)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
