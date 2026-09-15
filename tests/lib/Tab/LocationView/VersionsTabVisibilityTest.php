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
    private static ?Content $exampleContent = null;

    private static function getExampleContent(): Content
    {
        if (self::$exampleContent === null) {
            self::$exampleContent = self::createStub(Content::class);
        }

        return self::$exampleContent;
    }

    protected function createTabForVisibilityInGivenUserModeTest(UserSettingService $userSettingService): TabInterface
    {
        $permissionResolver = $this->createMock(PermissionResolver::class);
        $permissionResolver
            ->method('canUser')
            ->with(
                'content',
                'versionread',
                self::getExampleContent()
            )
            ->willReturn(true);

        return new VersionsTab(
            $this->createStub(Environment::class),
            $this->createStub(TranslatorInterface::class),
            $this->createStub(DatasetFactory::class),
            $this->createStub(FormFactory::class),
            $permissionResolver,
            $this->createStub(UserService::class),
            $userSettingService,
            $this->createStub(EventDispatcherInterface::class),
        );
    }

    public static function dataProviderForTestTabVisibilityInGivenUserMode(): iterable
    {
        yield 'focus mode on' => [
            FocusMode::FOCUS_MODE_ON,
            ['content' => self::getExampleContent()],
            false,
        ];

        yield 'focus mode off' => [
            FocusMode::FOCUS_MODE_OFF,
            ['content' => self::getExampleContent()],
            true,
        ];
    }
}
