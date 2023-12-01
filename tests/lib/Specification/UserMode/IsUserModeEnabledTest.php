<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Specification\UserMode;

use Ibexa\AdminUi\Specification\UserMode\IsUserModeEnabled;
use Ibexa\AdminUi\UserSetting\UserMode;
use Ibexa\User\UserSetting\UserSetting;
use Ibexa\User\UserSetting\UserSettingService;
use PHPUnit\Framework\TestCase;

final class IsUserModeEnabledTest extends TestCase
{
    /**
     * @dataProvider dataProviderForIsSatisfiedBy
     */
    public function testIsSatisfiedBy(string $userMode, string $value, bool $expectedResult): void
    {
        self::assertEquals(
            $expectedResult,
            (new IsUserModeEnabled($userMode))->isSatisfiedBy($value)
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
        $userSettingService->method('getUserSetting')->with(UserMode::IDENTIFIER)->willReturn($userSetting);

        self::assertEquals(
            $expectedResult,
            IsUserModeEnabled::fromUserSettings($userSettingService)->isSatisfiedBy($value)
        );
    }

    /**
     * @return iterable<array{string, string, bool}>
     */
    public function dataProviderForIsSatisfiedBy(): iterable
    {
        yield [UserMode::SMART, UserMode::SMART, true];
        yield [UserMode::SMART, UserMode::EXPERT, false];
        yield [UserMode::EXPERT, UserMode::SMART, false];
        yield [UserMode::EXPERT, UserMode::EXPERT, true];
    }
}
