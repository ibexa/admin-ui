<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator\Constraints;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Specification\Content\ContentHaveUniqueRelation;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class LocationHaveUniqueAssetRelationValidator extends ConstraintValidator
{
    public function __construct(private readonly ContentService $contentService)
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

        $haveUniqueRelation = new ContentHaveUniqueRelation($this->contentService);
        try {
            if (!$haveUniqueRelation->isSatisfiedBy($location->getContent())) {
                $this->context->addViolation($constraint->message);
            }
        } catch (InvalidArgumentException $e) {
            $this->context->addViolation($e->getMessage());
        } catch (UnauthorizedException $e) {
            $this->context->addViolation($e->getMessage());
        }
    }
}
