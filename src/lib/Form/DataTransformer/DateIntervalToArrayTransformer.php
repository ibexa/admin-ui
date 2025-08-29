<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use DateInterval;
use Exception;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Data transformer from PHP DateInterval to array for form inputs.
 */
final readonly class DateIntervalToArrayTransformer implements DataTransformerInterface
{
    /**
     * Transforms a date interval into an array of date interval elements.
     *
     * @param \DateInterval|null $value date interval
     *
     * @return array<string, string> date interval elements
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException If the given value is not an instance of DateInterval
     */
    public function transform(mixed $value): array
    {
        if ($value === null) {
            return [
                'year' => '0',
                'month' => '0',
                'day' => '0',
                'hour' => '0',
                'minute' => '0',
                'second' => '0',
            ];
        }

        return [
            'year' => $value->format('%y'),
            'month' => $value->format('%m'),
            'day' => $value->format('%d'),
            'hour' => $value->format('%h'),
            'minute' => $value->format('%i'),
            'second' => $value->format('%s'),
        ];
    }

    /**
     * Transforms an array of date interval elements into a date interval.
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException if the given value is not an array,
     *                                       or if the value could not be transformed
     */
    public function reverseTransform(mixed $value): ?DateInterval
    {
        if (null === $value) {
            return null;
        }

        if (!is_array($value)) {
            throw new TransformationFailedException('Expected an array.');
        }

        if ('' === implode('', $value)) {
            return null;
        }

        // List the fields that are not set as keys in $value
        $emptyFields = array_diff(
            ['year', 'month', 'day', 'hour', 'minute', 'second'],
            array_keys($value)
        );

        if (count($emptyFields) > 0) {
            throw new TransformationFailedException(
                sprintf('Fields "%s" should not be empty', implode('", "', $emptyFields))
            );
        }

        if (isset($value['month']) && !ctype_digit((string)$value['month'])) {
            throw new TransformationFailedException('This month is invalid');
        }

        if (isset($value['day']) && !ctype_digit((string)$value['day'])) {
            throw new TransformationFailedException('This day is invalid');
        }

        if (isset($value['year']) && !ctype_digit((string)$value['year'])) {
            throw new TransformationFailedException('This year is invalid');
        }

        if (!empty($value['month']) && !empty($value['day']) && !empty($value['year']) &&
            false === checkdate((int)$value['month'], (int)$value['day'], (int)$value['year'])) {
            throw new TransformationFailedException('This is an invalid date');
        }

        if (isset($value['hour']) && !ctype_digit((string)$value['hour'])) {
            throw new TransformationFailedException('This hour is invalid');
        }

        if (isset($value['minute']) && !ctype_digit((string)$value['minute'])) {
            throw new TransformationFailedException('This minute is invalid');
        }

        if (isset($value['second']) && !ctype_digit((string)$value['second'])) {
            throw new TransformationFailedException('This second is invalid');
        }

        try {
            $dateInterval = new DateInterval(
                sprintf(
                    'P%sY%sM%sDT%sH%sM%sS',
                    empty($value['year']) ? '0' : $value['year'],
                    empty($value['month']) ? '0' : $value['month'],
                    empty($value['day']) ? '0' : $value['day'],
                    empty($value['hour']) ? '0' : $value['hour'],
                    empty($value['minute']) ? '0' : $value['minute'],
                    empty($value['second']) ? '0' : $value['second']
                )
            );
        } catch (Exception $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }

        return $dateInterval;
    }
}
