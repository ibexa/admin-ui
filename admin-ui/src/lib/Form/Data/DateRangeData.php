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
    private ?DateTimeInterface $min;

    private ?DateTimeInterface $max;

    public function __construct(?DateTimeInterface $min = null, ?DateTimeInterface $max = null)
    {
        $this->min = $min;
        $this->max = $max;
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
