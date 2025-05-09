<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator\Constraints;

use Ibexa\AdminUi\Specification\Location\IsWithinCopySubtreeLimit;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LocationIsWithinCopySubtreeLimitValidator extends ConstraintValidator
{
    private LocationService $locationService;

    private ConfigResolverInterface $configResolver;

    public function __construct(
        LocationService $locationService,
        ConfigResolverInterface $configResolver
    ) {
        $this->locationService = $locationService;
        $this->configResolver = $configResolver;
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
            return;
        }

        $isWithinCopySubtreeLimit = new IsWithinCopySubtreeLimit(
            $this->configResolver->getParameter('subtree_operations.copy_subtree.limit'),
            $this->locationService
        );
        try {
            if (!$isWithinCopySubtreeLimit->isSatisfiedBy($location)) {
                $this
                    ->context
                    ->buildViolation($constraint->message)
                    ->setParameter(
                        '%currentLimit%',
                        (string)$this->configResolver->getParameter('subtree_operations.copy_subtree.limit')
                    )
                    ->addViolation();
            }
        } catch (InvalidArgumentException $e) {
            $this->context->addViolation($e->getMessage());
        }
    }
}
