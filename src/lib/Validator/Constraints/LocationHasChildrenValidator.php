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

class LocationHasChildrenValidator extends ConstraintValidator
{
    private LocationService $locationService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     */
    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

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

        $hasChildren = new HasChildren($this->locationService);

        if (!$hasChildren->isSatisfiedBy($location)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
