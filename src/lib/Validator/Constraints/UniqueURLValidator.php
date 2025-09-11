<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator\Constraints;

use Ibexa\AdminUi\Form\Data\URL\URLUpdateData;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\URLService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class UniqueURLValidator extends ConstraintValidator
{
    public function __construct(private readonly URLService $urlService)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof URLUpdateData || $value->url === null) {
            return;
        }

        try {
            $url = $this->urlService->loadByUrl($value->url);

            if ($url->getId() === $value->id) {
                return;
            }

            $this->context->buildViolation($constraint->message)
                ->atPath('url')
                ->setParameter('%url%', $value->url)
                ->addViolation();
        } catch (NotFoundException) {
            // Do nothing
        }
    }
}
