<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Menu;

use Ibexa\AdminUi\Menu\MainMenuBuilder;
use Ibexa\AdminUi\UserSetting\FocusMode;
use Ibexa\Contracts\AdminUi\Menu\MenuItemFactoryInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Security\UserInterface;
use Ibexa\User\UserSetting\UserSetting;
use Ibexa\User\UserSetting\UserSettingService;
use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MainMenuBuilerTest extends TestCase
{
    /** @var \Ibexa\Contracts\AdminUi\Menu\MenuItemFactoryInterface */
    private MockObject $factory;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private MockObject $eventDispatcher;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private MockObject $configResolver;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    private MockObject $permissionResolver;

    /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface */
    private MockObject $tokenStorage;

    /** @var \Ibexa\User\UserSetting\UserSettingService&\PHPUnit\Framework\MockObject\MockObject */
    private UserSettingService $userSettingService;

    protected function setUp(): void
    {
        $knpFactory = $this->createMock(FactoryInterface::class);
        $knpFactory->method('createItem')
            ->willReturnCallback(static function (string $name) use ($knpFactory): \Knp\Menu\MenuItem {
                return new MenuItem($name, $knpFactory);
            })
        ;

        $parameterMap = [
            ['location_ids.content_structure', null, null, 5],
            ['location_ids.media', null, null, 10],
            ['location_ids.users', null, null, 20],
        ];

        $itemMap = [
            ['root', [], new MenuItem('root', $knpFactory)],
            [
                MainMenuBuilder::ITEM_DASHBOARD,
                [
                    'route' => 'ibexa.dashboard',
                    'attributes' => [
                        'data-tooltip-placement' => 'right',
                        'data-tooltip-extra-class' => 'ibexa-tooltip--navigation',
                    ],
                    'extras' => [
                        'icon' => 'dashboard-clean',
                        'orderNumber' => 20,
                    ],
                ],
                new MenuItem(MainMenuBuilder::ITEM_DASHBOARD, $knpFactory),
            ],
            [
                MainMenuBuilder::ITEM_CONTENT,
                [
                    'attributes' => [
                        'data-tooltip-placement' => 'right',
                        'data-tooltip-extra-class' => 'ibexa-tooltip--navigation',
                    ],
                    'extras' => [
                        'icon' => 'hierarchy',
                        'orderNumber' => 40,
                    ],
                ],
                new MenuItem(MainMenuBuilder::ITEM_CONTENT, $knpFactory),
            ],
            [
                MainMenuBuilder::ITEM_ADMIN,
                [
                    'attributes' => [
                        'data-tooltip-placement' => 'right',
                        'data-tooltip-extra-class' => 'ibexa-tooltip--navigation',
                    ],
                    'extras' => [
                        'separate' => true,
                        'bottom_item' => true,
                        'icon' => 'settings-block',
                        'orderNumber' => 140,
                    ],
                ],
                new MenuItem(MainMenuBuilder::ITEM_ADMIN, $knpFactory),
            ],
            [
                MainMenuBuilder::ITEM_BOOKMARKS,
                [
                    'route' => 'ibexa.bookmark.list',
                    'attributes' => [
                        'data-tooltip-placement' => 'right',
                        'data-tooltip-extra-class' => 'ibexa-tooltip--navigation',
                    ],
                    'extras' => [
                        'bottom_item' => true,
                        'icon' => 'bookmark',
                        'orderNumber' => 160,
                    ],
                ],
                new MenuItem(MainMenuBuilder::ITEM_BOOKMARKS, $knpFactory),
            ],
            [
                MainMenuBuilder::ITEM_TRASH,
                [
                    'route' => 'ibexa.trash.list',
                    'attributes' => [
                        'data-tooltip-placement' => 'right',
                        'data-tooltip-extra-class' => 'ibexa-tooltip--navigation',
                    ],
                    'extras' => [
                        'bottom_item' => true,
                        'icon' => 'trash',
                        'orderNumber' => 180,
                    ],
                ],
                new MenuItem(MainMenuBuilder::ITEM_TRASH, $knpFactory),
            ],
        ];
        foreach (MainMenuBuilder::ITEM_ADMIN_OPTIONS as $id => $options) {
            $itemMap[] = [
                $id,
                $options,
                new MenuItem($id, $knpFactory),
            ];
        }

        $this->factory = $this->createMock(MenuItemFactoryInterface::class);
        $this->factory->method('createItem')->willReturnMap($itemMap);

        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->configResolver->method('getParameter')->willReturnMap($parameterMap);
        $this->permissionResolver = $this->createMock(PermissionResolver::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $token = new TestBrowserToken([], $this->createMock(UserInterface::class));
        $this->tokenStorage->method('getToken')->willReturn($token);

        $userSetting = $this->createMock(UserSetting::class);
        $userSetting->method('__get')->with('value')->willReturn(FocusMode::FOCUS_MODE_OFF);

        $this->userSettingService = $this->createMock(UserSettingService::class);
        $this->userSettingService->method('getUserSetting')->with(FocusMode::IDENTIFIER)->willReturn($userSetting);
    }

    protected function tearDown(): void
    {
        unset($this->factory, $this->eventDispatcher, $this->configResolver, $this->permissionResolver, $this->tokenStorage, $this->userSettingService);
    }

    public function testCreateMenuForUserWithAdministratePolicy(): void
    {
        $accessMap = [
            ['setup', 'administrate', null, true],
        ];

        $this->permissionResolver->method('hasAccess')->willReturnMap($accessMap);

        $menuBuilder = new MainMenuBuilder(
            $this->factory,
            $this->eventDispatcher,
            $this->configResolver,
            $this->permissionResolver,
            $this->tokenStorage,
            $this->userSettingService
        );
        $menu = $menuBuilder->createStructure([]);

        $children = $menu->getChildren();

        $this->assertMenuHasAllItems($children);
    }

    public function testCreateMenuForUserWithoutAdministratePolicy(): void
    {
        $accessMap = [
            ['setup', 'administrate', null, false],
        ];

        $this->permissionResolver->method('hasAccess')->willReturnMap($accessMap);

        $menuBuilder = new MainMenuBuilder(
            $this->factory,
            $this->eventDispatcher,
            $this->configResolver,
            $this->permissionResolver,
            $this->tokenStorage,
            $this->userSettingService
        );
        $menu = $menuBuilder->createStructure([]);

        $children = $menu->getChildren();
        $this->assertMenuHasAllItems($children);
    }

    private function assertMenuHasAllItems(array $menu): void
    {
        self::assertArrayHasKey(MainMenuBuilder::ITEM_CONTENT, $menu);
        self::assertArrayHasKey(MainMenuBuilder::ITEM_ADMIN, $menu);
        self::assertArrayHasKey(MainMenuBuilder::ITEM_BOOKMARKS, $menu);
        self::assertArrayHasKey(MainMenuBuilder::ITEM_TRASH, $menu);
    }
}
