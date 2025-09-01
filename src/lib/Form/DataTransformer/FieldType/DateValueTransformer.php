<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer\FieldType;

use Ibexa\Core\FieldType\Date\Value;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * DataTransformer for Date\Value.
 */
final readonly class DateValueTransformer implements DataTransformerInterface
{
    /**
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function transform(mixed $value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Value) {
            throw new TransformationFailedException(
                sprintf('Received %s instead of %s', gettype($value), Value::class)
            );
        }

        if (null === $value->date) {
            return null;
        }

        return $value->date->getTimestamp() + $value->date->getOffset();
    }

    /**
     * @param int|mixed $value
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function reverseTransform(mixed $value): ?Value
    {
        if (empty($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            throw new TransformationFailedException(
                sprintf('Received %s instead instead of a numeric value', gettype($value))
            );
        }

        return Value::fromTimestamp($value);
    }
}
