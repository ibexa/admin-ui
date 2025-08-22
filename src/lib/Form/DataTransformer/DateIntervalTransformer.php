<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use DateInterval;
use DateTime;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Translates timestamp and DataInterval to domain specific timestamp date range.
 */
class DateIntervalTransformer implements DataTransformerInterface
{
    /**
     * @param array|null $value
     *
     * @return array|null
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function transform($value)
    {
        return null;
    }

    /**
     * @param array<mixed>|null $value
     *
     * @return array<string, int>
     */
    public function reverseTransform($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        if (
            !array_key_exists('date_interval', $value)
            || !array_key_exists('start_date', $value)
            || !array_key_exists('end_date', $value)
        ) {
            throw new TransformationFailedException(
                "Invalid data. On of the array keys is missing 'date_interval', 'start_date' or 'end_date'"
            );
        }

        $startDateTimestamp = $value['start_date'] ?? null;
        $endDateTimestamp = $value['end_date'] ?? null;

        $dateInterval = $value['date_interval'];
        if (!empty($dateInterval)) {
            $interval = new DateInterval($dateInterval);

            $date = new DateTime();
            $endDateTimestamp = $date->getTimestamp();

            $date->setTimestamp($endDateTimestamp);
            $date->sub($interval);

            $startDateTimestamp = $date->getTimestamp();
        }

        return ['start_date' => $startDateTimestamp, 'end_date' => $endDateTimestamp];
    }
}

class_alias(DateIntervalTransformer::class, 'EzSystems\EzPlatformAdminUi\Form\DataTransformer\DateIntervalTransformer');
