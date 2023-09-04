<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use DateInterval;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FormatIntervalExtension extends AbstractExtension implements TranslationContainerInterface
{
    private const INTERVAL_PARTS = [
        'y' => 'years',
        'm' => 'months',
        'd' => 'days',
        'h' => 'hours',
        'i' => 'minutes',
        's' => 'seconds',
    ];

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('format_interval', [$this, 'formatInterval']),
        ];
    }

    public function formatInterval(string $periodFormat)
    {
        $interval = new DateInterval($periodFormat);
        $parts = [];

        foreach (self::INTERVAL_PARTS as $part => $name) {
            if ($interval->$part > 0) {
                $parts[] = $this->translator->trans(
                    /** @ignore */
                    sprintf('interval.format.%s', $name),
                    ['%count%' => $interval->$part],
                    'ibexa_time_diff'
                );
            }
        }

        return implode(' ', $parts);
    }

    public static function getTranslationMessages(): array
    {
        return [
            (new Message('interval.format.seconds', 'ibexa_time_diff'))->setDesc('1 second|%count% seconds'),
            (new Message('interval.format.minutes', 'ibexa_time_diff'))->setDesc('1 minute|%count% minutes'),
            (new Message('interval.format.hours', 'ibexa_time_diff'))->setDesc('1 hour|%count% hours'),
            (new Message('interval.format.days', 'ibexa_time_diff'))->setDesc('1 day|%count% days'),
            (new Message('interval.format.months', 'ibexa_time_diff'))->setDesc('1 month|%count% months'),
            (new Message('interval.format.years', 'ibexa_time_diff'))->setDesc('1 year|%count% years'),
        ];
    }
}

class_alias(FormatIntervalExtension::class, 'EzSystems\EzPlatformAdminUiBundle\Templating\Twig\FormatIntervalExtension');
