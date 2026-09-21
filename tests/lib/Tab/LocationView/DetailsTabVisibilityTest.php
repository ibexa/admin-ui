<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Tab\LocationView\DetailsTab;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\AdminUi\UserSetting\FocusMode;
use Ibexa\Contracts\AdminUi\Tab\TabInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\User\UserSetting\UserSettingService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class DetailsTabVisibilityTest extends AbstractTabVisibilityTestCase
{
    protected function createTabForVisibilityInGivenUserModeTest(UserSettingService $userSettingService): TabInterface
    {
        return new DetailsTab(
            self::createStub(Environment::class),
            self::createStub(TranslatorInterface::class),
            self::createStub(SectionService::class),
            self::createStub(DatasetFactory::class),
            self::createStub(FormFactoryInterface::class),
            self::createStub(PermissionResolver::class),
            $userSettingService,
            self::createStub(EventDispatcherInterface::class)
        );
    }

    public static function dataProviderForTestTabVisibilityInGivenUserMode(): iterable
    {
        yield 'focus mode on' => [FocusMode::FOCUS_MODE_ON, [], false];
        yield 'focus mode off' => [FocusMode::FOCUS_MODE_OFF, [], true];
    }
}
