<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Specification\UserMode;

use Ibexa\AdminUi\Specification\UserMode\IsFocusModeEnabled;
use Ibexa\AdminUi\UserSetting\FocusMode;
use Ibexa\User\UserSetting\UserSetting;
use Ibexa\User\UserSetting\UserSettingService;
use PHPUnit\Framework\TestCase;

final class IsFocusModeEnabledTest extends TestCase
{
    /**
     * @dataProvider dataProviderForIsSatisfiedBy
     */
    public function testIsSatisfiedBy(string $userMode, string $value, bool $expectedResult): void
    {
        self::assertEquals(
            $expectedResult,
            (new IsFocusModeEnabled($userMode))->isSatisfiedBy($value)
        );
    }

    /**
     * @dataProvider dataProviderForIsSatisfiedBy
     */
    public function testFromUserSetting(string $userMode, string $value, bool $expectedResult): void
    {
        $userSetting = $this->createMock(UserSetting::class);
        $userSetting->method('__get')->with('value')->willReturn($userMode);

        $userSettingService = $this->createMock(UserSettingService::class);
        $userSettingService->method('getUserSetting')->with(FocusMode::IDENTIFIER)->willReturn($userSetting);

        self::assertEquals(
            $expectedResult,
            IsFocusModeEnabled::fromUserSettings($userSettingService)->isSatisfiedBy($value)
        );
    }

    /**
     * @return iterable<array{string, string, bool}>
     */
    public function dataProviderForIsSatisfiedBy(): iterable
    {
        yield [FocusMode::FOCUS_MODE_ON, FocusMode::FOCUS_MODE_ON, true];
        yield [FocusMode::FOCUS_MODE_ON, FocusMode::FOCUS_MODE_OFF, false];
        yield [FocusMode::FOCUS_MODE_OFF, FocusMode::FOCUS_MODE_ON, false];
        yield [FocusMode::FOCUS_MODE_OFF, FocusMode::FOCUS_MODE_OFF, true];
    }
}
