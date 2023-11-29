<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\AdminUi\UserSetting\UserMode;
use Ibexa\User\UserSetting\UserSettingService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UserModeExtension extends AbstractExtension
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
                'ibexa_is_expert_mode',
                fn (): bool => $this->isModeEnabled(UserMode::EXPERT)
            ),
            new TwigFunction(
                'ibexa_is_smart_mode',
                fn (): bool => $this->isModeEnabled(UserMode::SMART)
            ),
        ];
    }

    private function isModeEnabled(string $mode): bool
    {
        return $this->userService->getUserSetting(UserMode::IDENTIFIER)->value === $mode;
    }
}
