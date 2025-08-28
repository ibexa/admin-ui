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

/**
 * @phpstan-implements \Symfony\Component\Form\DataTransformerInterface<\DateTimeInterface, int>
 */
final readonly class DateTimePickerTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): ?int
    {
        return $value?->getTimestamp();
    }

    public function reverseTransform(mixed $value): ?DateTimeInterface
    {
        if (empty($value)) {
            return null;
        }

        return DateTime::createFromFormat('U', (string)$value) ?: null;
    }
}
