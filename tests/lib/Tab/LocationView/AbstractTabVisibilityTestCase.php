<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\UserSetting\FocusMode;
use Ibexa\Contracts\AdminUi\Tab\TabInterface;
use Ibexa\User\UserSetting\UserSetting;
use Ibexa\User\UserSetting\UserSettingService;
use PHPUnit\Framework\TestCase;

abstract class AbstractTabVisibilityTestCase extends TestCase
{
    /**
     * @dataProvider dataProviderForTestTabVisibilityInGivenUserMode
     *
     * @param array<string, mixed> $parameters
     */
    final public function testTabVisibilityInGivenUserMode(string $userMode, array $parameters, bool $expectedResult): void
    {
        $userSetting = $this->createMock(UserSetting::class);
        $userSetting->method('__get')->with('value')->willReturn($userMode);

        $userSettingService = $this->createMock(UserSettingService::class);
        $userSettingService->method('getUserSetting')->with(FocusMode::IDENTIFIER)->willReturn($userSetting);

        $actualResult = $this->createTabForVisibilityInGivenUserModeTest($userSettingService)->evaluate($parameters);

        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return iterable<string, array{string, array<string, mixed>, bool}>
     */
    abstract public function dataProviderForTestTabVisibilityInGivenUserMode(): iterable;

    /**
     * @return \Ibexa\Contracts\AdminUi\Tab\TabInterface&\Ibexa\Contracts\AdminUi\Tab\ConditionalTabInterface
     */
    abstract protected function createTabForVisibilityInGivenUserModeTest(
        UserSettingService $userSettingService
    ): TabInterface;
}
