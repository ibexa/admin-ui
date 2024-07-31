<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\AdminUi\UserSetting\FocusMode;
use Ibexa\User\UserSetting\UserSettingService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FocusModeExtension extends AbstractExtension
{
    private UserSettingService $userService;

    public function __construct(UserSettingService $userService)
    {
        $this->userService = $userService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ibexa_is_focus_mode_off',
                fn (): bool => $this->isModeEnabled(FocusMode::FOCUS_MODE_OFF)
            ),
            new TwigFunction(
                'ibexa_is_focus_mode_on',
                fn (): bool => $this->isModeEnabled(FocusMode::FOCUS_MODE_ON)
            ),
        ];
    }

    private function isModeEnabled(string $mode): bool
    {
        return $this->userService->getUserSetting(FocusMode::IDENTIFIER)->value === $mode;
    }
}
