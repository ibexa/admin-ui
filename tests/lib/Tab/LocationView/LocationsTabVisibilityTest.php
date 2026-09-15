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
            $this->createStub(Environment::class),
            $this->createStub(TranslatorInterface::class),
            $this->createStub(FormFactory::class),
            $this->createStub(UrlGeneratorInterface::class),
            $this->createStub(PermissionResolver::class),
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(SearchService::class),
            $this->createStub(RequestStack::class),
            new Mapper($this->createStub(ValueFactory::class)),
            $this->createStub(ConfigResolverInterface::class),
            $userSettingService
        );
    }

    public static function dataProviderForTestTabVisibilityInGivenUserMode(): iterable
    {
        yield 'focus mode on' => [FocusMode::FOCUS_MODE_ON, [], false];
        yield 'focus mode off' => [FocusMode::FOCUS_MODE_OFF, [], true];
    }
}
