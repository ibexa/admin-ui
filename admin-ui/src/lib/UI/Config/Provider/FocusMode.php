<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\AdminUi\Specification\UserMode\IsFocusModeEnabled;
use Ibexa\AdminUi\UserSetting\FocusMode as FocusModeSetting;
use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\User\UserSetting\UserSettingService;

final class FocusMode implements ProviderInterface
{
    private UserSettingService $userSettingService;

    public function __construct(UserSettingService $userSettingService)
    {
        $this->userSettingService = $userSettingService;
    }

    public function getConfig(): bool
    {
        return IsFocusModeEnabled::fromUserSettings($this->userSettingService)->isSatisfiedBy(FocusModeSetting::FOCUS_MODE_ON);
    }
}
