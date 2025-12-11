<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data;

use DateTimeInterface;

final class DateRangeData
{
    public function __construct(
        private ?DateTimeInterface $min = null,
        private ?DateTimeInterface $max = null
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->min === null && $this->max === null;
    }

    public function getMin(): ?DateTimeInterface
    {
        return $this->min;
    }

    public function setMin(?DateTimeInterface $min): void
    {
        $this->min = $min;
    }

    public function getMax(): ?DateTimeInterface
    {
        return $this->max;
    }

    public function setMax(?DateTimeInterface $max): void
    {
        $this->max = $max;
    }
}
