<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Tab\LocationView\VersionsTab;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\AdminUi\UserSetting\FocusMode;
use Ibexa\Contracts\AdminUi\Tab\TabInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\User\UserSetting\UserSettingService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class VersionsTabVisibilityTest extends AbstractTabVisibilityTestCase
{
    protected function createTabForVisibilityInGivenUserModeTest(UserSettingService $userSettingService): TabInterface
    {
        $permissionResolver = $this->createMock(PermissionResolver::class);
        $permissionResolver
            ->method('canUser')
            ->with(
                'content',
                'versionread',
                self::isInstanceOf(Content::class)
            )
            ->willReturn(true);

        return new VersionsTab(
            self::createStub(Environment::class),
            self::createStub(TranslatorInterface::class),
            self::createStub(DatasetFactory::class),
            self::createStub(FormFactory::class),
            $permissionResolver,
            self::createStub(UserService::class),
            $userSettingService,
            self::createStub(EventDispatcherInterface::class),
        );
    }

    public static function dataProviderForTestTabVisibilityInGivenUserMode(): iterable
    {
        yield 'focus mode on' => [
            FocusMode::FOCUS_MODE_ON,
            ['content' => self::createStub(Content::class)],
            false,
        ];

        yield 'focus mode off' => [
            FocusMode::FOCUS_MODE_OFF,
            ['content' => self::createStub(Content::class)],
            true,
        ];
    }
}
