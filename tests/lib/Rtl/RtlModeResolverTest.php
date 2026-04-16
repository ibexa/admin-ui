<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Rtl;

use Ibexa\AdminUi\Rtl\RtlModeResolver;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\User\UserSetting\UserSetting;
use Ibexa\User\UserSetting\UserSettingService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RtlModeResolverTest extends TestCase
{
    private const RTL_LANGUAGES = ['ar', 'he', 'fa'];

    private UserSettingService&MockObject $userSettingService;

    protected function setUp(): void
    {
        $this->userSettingService = $this->createMock(UserSettingService::class);
    }

    public function testIsRtlReturnsTrueForRtlLanguage(): void
    {
        $this->mockUserLanguage('ar');

        self::assertTrue($this->createResolver()->isRtl());
    }

    public function testIsRtlReturnsFalseForLtrLanguage(): void
    {
        $this->mockUserLanguage('en');

        self::assertFalse($this->createResolver()->isRtl());
    }

    public function testIsRtlReturnsFalseWhenRtlLanguagesListIsEmpty(): void
    {
        $this->mockUserLanguage('ar');

        $resolver = new RtlModeResolver($this->userSettingService, []);

        self::assertFalse($resolver->isRtl());
    }

    public function testIsRtlReturnsFalseWhenUserSettingThrowsInvalidArgumentException(): void
    {
        $this->userSettingService
            ->method('getUserSetting')
            ->willThrowException($this->createMock(InvalidArgumentException::class));

        self::assertFalse($this->createResolver()->isRtl());
    }

    public function testIsRtlReturnsFalseWhenUserSettingThrowsUnauthorizedException(): void
    {
        $this->userSettingService
            ->method('getUserSetting')
            ->willThrowException($this->createMock(UnauthorizedException::class));

        self::assertFalse($this->createResolver()->isRtl());
    }

    private function createResolver(): RtlModeResolver
    {
        return new RtlModeResolver($this->userSettingService, self::RTL_LANGUAGES);
    }

    private function mockUserLanguage(string $language): void
    {
        $userSetting = $this->createMock(UserSetting::class);
        $userSetting
            ->expects(self::once())
            ->method('getValue')
            ->willReturn($language);

        $this->userSettingService
            ->expects(self::once())
            ->method('getUserSetting')
            ->with('language')
            ->willReturn($userSetting);
    }
}
