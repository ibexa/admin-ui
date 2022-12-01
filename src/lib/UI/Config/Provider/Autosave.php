<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\AdminUi\UserSetting\Autosave as AutosaveSetting;
use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\User\UserSetting\UserSettingService;

class Autosave implements ProviderInterface
{
    /** @var \Ibexa\User\UserSetting\UserSettingService */
    private $userSettingService;

    public function __construct(
        UserSettingService $userSettingService
    ) {
        $this->userSettingService = $userSettingService;
    }

    public function getConfig(): array
    {
        $isEnabled = $this->userSettingService->getUserSetting('autosave')->value === AutosaveSetting::ENABLED_OPTION;
        $interval = (int)$this->userSettingService->getUserSetting('autosave_interval')->value * 1000;

        return [
            'enabled' => $isEnabled,
            'interval' => $interval,
        ];
    }
}

class_alias(Autosave::class, 'EzSystems\EzPlatformAdminUi\UI\Config\Provider\Autosave');
