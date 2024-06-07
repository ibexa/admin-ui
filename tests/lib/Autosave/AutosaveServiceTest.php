<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Autosave;

use Ibexa\AdminUi\Autosave\AutosaveService;
use Ibexa\AdminUi\UserSetting\Autosave;
use Ibexa\AdminUi\UserSetting\AutosaveInterval;
use Ibexa\User\UserSetting\UserSetting;
use Ibexa\User\UserSetting\UserSettingService;
use PHPUnit\Framework\TestCase;

final class AutosaveServiceTest extends TestCase
{
    /** @var \Ibexa\User\UserSetting\UserSettingService&\PHPUnit\Framework\MockObject\MockObject */
    private UserSettingService $userSettingService;

    private AutosaveService $autosaveService;

    protected function setUp(): void
    {
        $this->userSettingService = $this->createMock(UserSettingService::class);
        $this->autosaveService = new AutosaveService($this->userSettingService);
    }

    public function testIsEnabled(): void
    {
        $this->userSettingService
            ->method('getUserSetting')
            ->with(Autosave::IDENTIFIER)
            ->willReturn($this->createUserSettingWithValue(Autosave::ENABLED_OPTION));

        self::assertTrue($this->autosaveService->isEnabled());
    }

    public function testGetInterval(): void
    {
        $this->userSettingService
            ->method('getUserSetting')
            ->with(AutosaveInterval::IDENTIFIER)
            ->willReturn($this->createUserSettingWithValue('30'));

        self::assertEquals(30000, $this->autosaveService->getInterval());
    }

    public function testIsInProgress(): void
    {
        self::assertFalse($this->autosaveService->isInProgress());
        $this->autosaveService->setInProgress(true);
        self::assertTrue($this->autosaveService->isInProgress());
    }

    private function createUserSettingWithValue(string $value): UserSetting
    {
        return new UserSetting([
            'value' => $value,
        ]);
    }
}
