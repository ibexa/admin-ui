<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Autosave;

use Ibexa\AdminUi\UserSetting\Autosave;
use Ibexa\AdminUi\UserSetting\AutosaveInterval;
use Ibexa\Contracts\AdminUi\Autosave\AutosaveServiceInterface;
use Ibexa\User\UserSetting\UserSettingService;

final class AutosaveService implements AutosaveServiceInterface
{
    private UserSettingService $userSettingService;

    private bool $inProgress = false;

    public function __construct(UserSettingService $userSettingService)
    {
        $this->userSettingService = $userSettingService;
    }

    public function isEnabled(): bool
    {
        return $this->userSettingService->getUserSetting(Autosave::IDENTIFIER)->value === Autosave::ENABLED_OPTION;
    }

    /**
     * Returns autosave interval in milliseconds.
     */
    public function getInterval(): int
    {
        return (int)$this->userSettingService->getUserSetting(AutosaveInterval::IDENTIFIER)->value * 1000;
    }

    public function isInProgress(): bool
    {
        return $this->inProgress;
    }

    public function setInProgress(bool $isInProgress): void
    {
        $this->inProgress = $isInProgress;
    }
}
