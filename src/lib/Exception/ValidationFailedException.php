<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Exception;

use Exception;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidationFailedException extends InvalidArgumentException
{
    private ConstraintViolationListInterface $errors;

    public function __construct(
        string $argumentName,
        ConstraintViolationListInterface $errors,
        Exception $previous = null
    ) {
        parent::__construct($this->createMessage($argumentName, $errors), 0, $previous);

        $this->errors = $errors;
    }

    public function getErrors(): ConstraintViolationListInterface
    {
        return $this->errors;
    }

    private function createMessage(string $argumentName, ConstraintViolationListInterface $errors): string
    {
        if ($errors->count() > 1) {
            return sprintf(
                "Argument '%s->%s' is invalid: %s",
                $argumentName,
                $errors->get(1)->getPropertyPath(),
                $errors->get(1)->getMessage()
            );
        }

        if ($errors->count() === 1) {
            return sprintf(
                "Argument '%s->%s' is invalid: %s",
                $argumentName,
                $errors->get(0)->getPropertyPath(),
                $errors->get(0)->getMessage()
            );
        }

        return sprintf(
            'Argument \'%s\' is invalid: %s and %d more errors',
            $argumentName,
            $errors->get(0)->getMessage(),
            $errors->count() - 1
        );
    }
}
