<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Validator\Builder\REST;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UniversalDiscoveryRequestValidatorBuilder
{
    private ContextualValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator->startContext();
    }

    public function validateLocationId(Request $request): self
    {
        $this->validator
            ->atPath('{locationId}')
            ->validate(
                $request->get('locationId'),
                new Assert\Type('numeric')
            );

        return $this;
    }

    public function build(): ContextualValidatorInterface
    {
        return $this->validator;
    }
}
