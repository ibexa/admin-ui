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

class DateFormat implements ProviderInterface
{
    /** @var \Ibexa\User\UserSetting\UserSettingService */
    protected $userSettingService;

    /** @var \Ibexa\User\UserSetting\Setting\DateTimeFormatSerializer */
    protected $dateTimeFormatSerializer;

    /**
     * @param \Ibexa\User\UserSetting\UserSettingService $userSettingService
     * @param \Ibexa\User\UserSetting\Setting\DateTimeFormatSerializer $dateTimeFormatSerializer
     */
    public function __construct(UserSettingService $userSettingService, DateTimeFormatSerializer $dateTimeFormatSerializer)
    {
        $this->userSettingService = $userSettingService;
        $this->dateTimeFormatSerializer = $dateTimeFormatSerializer;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getConfig(): array
    {
        $fullDateTimeFormat = $this->dateTimeFormatSerializer->deserialize(
            $this->userSettingService->getUserSetting('full_datetime_format')->value
        );

        $shortDateTimeFormat = $this->dateTimeFormatSerializer->deserialize(
            $this->userSettingService->getUserSetting('short_datetime_format')->value
        );

        return [
            'fullDateTime' => (string)$fullDateTimeFormat,
            'fullDate' => $fullDateTimeFormat->getDateFormat(),
            'fullTime' => $fullDateTimeFormat->getTimeFormat(),
            'shortDateTime' => (string)$shortDateTimeFormat,
            'shortDate' => $shortDateTimeFormat->getDateFormat(),
            'shortTime' => $shortDateTimeFormat->getTimeFormat(),
        ];
    }
}

class_alias(DateFormat::class, 'EzSystems\EzPlatformAdminUi\UI\Config\Provider\DateFormat');
