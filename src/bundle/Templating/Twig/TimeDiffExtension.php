<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use DateTime;
use DateTimeInterface;
use Ibexa\AdminUi\UI\Service\DateTimeFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class TimeDiffExtension extends AbstractExtension
{
    public function __construct(
        private readonly DateTimeFormatter $dateTimeFormatter
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'ibexa_datetime_diff',
                $this->diff(...),
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function diff(DateTimeInterface $from, ?DateTimeInterface $to = null): string
    {
        return $this->dateTimeFormatter->formatDiff($from, $to ?? new DateTime());
    }
}
