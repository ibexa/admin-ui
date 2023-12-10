<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Templating\Twig;

use Ibexa\AdminUi\UserSetting\FocusMode;
use Ibexa\Bundle\AdminUi\Templating\Twig\FocusModeExtension;
use Ibexa\User\UserSetting\UserSetting;
use Ibexa\User\UserSetting\UserSettingService;
use Twig\Test\IntegrationTestCase;

final class FocusModeExtensionTest extends IntegrationTestCase
{
    protected function getExtensions(): array
    {
        $userSetting = $this->createMock(UserSetting::class);
        $userSetting->method('__get')->with('value')->willReturn(FocusMode::FOCUS_MODE_ON);

        $userSettingService = $this->createMock(UserSettingService::class);
        $userSettingService->method('getUserSetting')->with(FocusMode::IDENTIFIER)->willReturn($userSetting);

        return [
            new FocusModeExtension($userSettingService),
        ];
    }

    protected function getFixturesDir(): string
    {
        return __DIR__ . '/_fixtures/focus_mode/';
    }
}
