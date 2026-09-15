<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Tab\LocationView;

use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Tab\LocationView\LocationsTab;
use Ibexa\AdminUi\UI\Value\Content\Location\Mapper;
use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\AdminUi\UserSetting\FocusMode;
use Ibexa\Contracts\AdminUi\Tab\TabInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\User\UserSetting\UserSettingService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class LocationsTabVisibilityTest extends AbstractTabVisibilityTestCase
{
    protected function createTabForVisibilityInGivenUserModeTest(UserSettingService $userSettingService): TabInterface
    {
        return new LocationsTab(
            self::createStub(Environment::class),
            self::createStub(TranslatorInterface::class),
            self::createStub(FormFactory::class),
            self::createStub(UrlGeneratorInterface::class),
            self::createStub(PermissionResolver::class),
            self::createStub(EventDispatcherInterface::class),
            self::createStub(SearchService::class),
            self::createStub(RequestStack::class),
            new Mapper(self::createStub(ValueFactory::class)),
            self::createStub(ConfigResolverInterface::class),
            $userSettingService
        );
    }

    public static function dataProviderForTestTabVisibilityInGivenUserMode(): iterable
    {
        yield 'focus mode on' => [FocusMode::FOCUS_MODE_ON, [], false];
        yield 'focus mode off' => [FocusMode::FOCUS_MODE_OFF, [], true];
    }
}
