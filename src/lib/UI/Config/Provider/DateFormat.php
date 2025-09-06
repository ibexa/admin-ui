<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\User\UserSetting\Setting\DateTimeFormatSerializer;
use Ibexa\User\UserSetting\UserSettingService;

final readonly class DateFormat implements ProviderInterface
{
    public function __construct(
        private UserSettingService $userSettingService,
        private DateTimeFormatSerializer $dateTimeFormatSerializer
    ) {
    }

    /**
     * @return array<string, string|null>
     */
    public function getConfig(): array
    {
        $fullDateTimeFormat = $this->dateTimeFormatSerializer->deserialize(
            $this->userSettingService->getUserSetting('full_datetime_format')->getValue()
        );

        $shortDateTimeFormat = $this->dateTimeFormatSerializer->deserialize(
            $this->userSettingService->getUserSetting('short_datetime_format')->getValue()
        );

        return [
            'fullDateTime' => $fullDateTimeFormat === null ? '' : (string)$fullDateTimeFormat,
            'fullDate' => $fullDateTimeFormat === null ? '' : $fullDateTimeFormat->getDateFormat(),
            'fullTime' => $fullDateTimeFormat === null ? '' : $fullDateTimeFormat->getTimeFormat(),
            'shortDateTime' => $shortDateTimeFormat === null ? '' : (string)$shortDateTimeFormat,
            'shortDate' => $shortDateTimeFormat === null ? '' : $shortDateTimeFormat->getDateFormat(),
            'shortTime' => $shortDateTimeFormat === null ? '' : $shortDateTimeFormat->getTimeFormat(),
        ];
    }
}
