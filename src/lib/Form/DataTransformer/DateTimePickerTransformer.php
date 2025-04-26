<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use DateTime;
use DateTimeInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DateTimePickerTransformer implements DataTransformerInterface
{
    /**
     * Transforms a value from the original representation to a transformed representation.
     *
     * @param mixed $value The value in the original representation
     *
     * @return mixed The value in the transformed representation
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException when the transformation fails
     */
    public function transform(mixed $value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof DateTimeInterface) {
            throw new TransformationFailedException(
                sprintf('Found %s instead of %s', get_debug_type($value), DateTimeInterface::class)
            );
        }

        return $value->getTimestamp();
    }

    /**
     * Transforms a value from the transformed representation to its original
     * representation.
     *
     * @param mixed $value The value in the transformed representation
     *
     * @return mixed The value in the original representation
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException when the transformation fails
     */
    public function reverseTransform(mixed $value): ?DateTime
    {
        if (empty($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            throw new TransformationFailedException(
                sprintf('Found %s instead of a numeric value', gettype($value))
            );
        }

        return DateTime::createFromFormat('U', (string)$value);
    }
}
