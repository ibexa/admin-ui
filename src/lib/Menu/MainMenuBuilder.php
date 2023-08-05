<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * KnpMenuBundle Menu Builder service implementation for AdminUI top menu.
 *
 * @see https://symfony.com/doc/current/bundles/KnpMenuBundle/menu_builder_service.html
 */
class MainMenuBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    /* Main Menu / Dashboard */
    public const ITEM_DASHBOARD = 'main__dashboard';
    public const ITEM_DRAFTS = 'main__drafts';

    /* Main Menu / Content */
    public const ITEM_CONTENT = 'main__content';
    public const ITEM_CONTENT_GROUP_SETTINGS = 'main__content__group_settings';
    public const ITEM_CONTENT__CONTENT_STRUCTURE = 'main__content__content_structure';
    public const ITEM_CONTENT__MEDIA = 'main__content__media';

    /* Main Menu / Admin */
    public const ITEM_ADMIN__SECTIONS = 'main__admin__sections';
    public const ITEM_ADMIN__ROLES = 'main__admin__roles';
    public const ITEM_ADMIN__LANGUAGES = 'main__admin__languages';
    public const ITEM_ADMIN__CONTENT_TYPES = 'main__admin__content_types';
    public const ITEM_ADMIN__USERS = 'main__admin__users';
    public const ITEM_ADMIN__OBJECT_STATES = 'main__admin__object_states';
    public const ITEM_ADMIN__URL_MANAGEMENT = 'main__admin__url_management';

    /* Main Menu / Bottom items */
    public const ITEM_ADMIN = 'main__admin';
    public const ITEM_BOOKMARKS = 'main__bookmarks';
    public const ITEM_TRASH = 'main__trash';

    public const ITEM_ADMIN_OPTIONS = [
        self::ITEM_ADMIN__SECTIONS => [
            'route' => 'ibexa.section.list',
            'extras' => [
                'routes' => [
                    'update' => 'ibexa.section.update',
                    'view' => 'ibexa.section.view',
                    'create' => 'ibexa.section.create',
                ],
                'orderNumber' => 20,
            ],
        ],
        self::ITEM_ADMIN__ROLES => [
            'route' => 'ibexa.role.list',
            'extras' => [
                'routes' => [
                    'update' => 'ibexa.role.update',
                    'view' => 'ibexa.role.view',
                    'create' => 'ibexa.role.create',
                    'policy_update' => 'ibexa.policy.update',
                    'policy_list' => 'ibexa.policy.list',
                    'policy_create' => 'ibexa.policy.create',
                    'policy_create_with_limitation' => 'ibexa.policy.create_with_limitation',
                ],
                'orderNumber' => 20,
            ],
        ],
        self::ITEM_ADMIN__LANGUAGES => [
            'route' => 'ibexa.language.list',
            'extras' => [
                'routes' => [
                    'edit' => 'ibexa.language.edit',
                    'view' => 'ibexa.language.view',
                    'create' => 'ibexa.language.create',
                ],
                'orderNumber' => 40,
            ],
        ],
        self::ITEM_ADMIN__CONTENT_TYPES => [
            'route' => 'ibexa.content_type_group.list',
            'extras' => [
                'routes' => [
                    'update' => 'ibexa.content_type_group.update',
                    'view' => 'ibexa.content_type_group.view',
                    'create' => 'ibexa.content_type_group.create',
                    'content_type_add' => 'ibexa.content_type.add',
                    'content_type_view' => 'ibexa.content_type.view',
                    'content_type_edit' => 'ibexa.content_type.edit',
                    'content_type_update' => 'ibexa.content_type.update',
                ],
                'orderNumber' => 50,
            ],
        ],
        self::ITEM_ADMIN__OBJECT_STATES => [
            'route' => 'ibexa.object_state.groups.list',
            'extras' => [
                'routes' => [
                    'group_list' => 'ibexa.object_state.groups.list',
                    'group_create' => 'ibexa.object_state.group.add',
                    'group_edit' => 'ibexa.object_state.group.update',
                    'group_view' => 'ibexa.object_state.group.view',
                    'state_create' => 'ibexa.object_state.state.add',
                    'state_view' => 'ibexa.object_state.state.view',
                    'state_edit' => 'ibexa.object_state.state.update',
                ],
                'orderNumber' => 60,
            ],
        ],
        self::ITEM_ADMIN__URL_MANAGEMENT => [
            'route' => 'ibexa.url_management',
            'extras' => [
                'routes' => [
                    'link_manager_edit' => 'ibexa.link_manager.edit',
                    'link_manager_view' => 'ibexa.link_manager.view',
                    'url_wildcard_edit' => 'ibexa.url_wildcard.update',
                ],
                'orderNumber' => 30,
            ],
        ],
    ];

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param \Ibexa\AdminUi\Menu\MenuItemFactory $factory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface $configResolver
     * @param \Ibexa\Contracts\Core\Repository\PermissionResolver $permissionResolver
     */
    public function __construct(
        MenuItemFactory $factory,
        EventDispatcherInterface $eventDispatcher,
        ConfigResolverInterface $configResolver,
        PermissionResolver $permissionResolver,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($factory, $eventDispatcher);

        $this->configResolver = $configResolver;
        $this->permissionResolver = $permissionResolver;
        $this->tokenStorage = $tokenStorage;
    }

    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::MAIN_MENU;
    }

    /**
     * @param array $options
     *
     * @return \Knp\Menu\ItemInterface
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function createStructure(array $options): ItemInterface
    {
        $token = $this->tokenStorage->getToken();

        /** @var \Knp\Menu\ItemInterface|\Knp\Menu\ItemInterface[] $menu */
        $menu = $this->createMenuItem('root');

        $contentMenu = $menu->addChild(self::ITEM_CONTENT, [
            'attributes' => [
                'data-tooltip-placement' => 'right',
                'data-tooltip-extra-class' => 'ibexa-tooltip--info-neon',
            ],
            'extras' => [
                'icon' => 'hierarchy',
                'orderNumber' => 40,
            ],
        ]);

        $contentMenu->addChild(self::ITEM_DASHBOARD, [
            'route' => 'ibexa.dashboard',
            'attributes' => [
                'data-tooltip-placement' => 'right',
                'data-tooltip-extra-class' => 'ibexa-tooltip--info-neon',
            ],
            'extras' => [
                'icon' => 'dashboard-clean',
                'orderNumber' => 20,
            ],
        ]);

        $contentMenu->addChild(self::ITEM_DRAFTS, [
            'route' => 'ibexa.dashboard',
            'routeParameters' => ['_fragment' => 'ibexa-tab-dashboard-my-my-drafts'],
            'attributes' => [
                'data-tooltip-placement' => 'right',
                'data-tooltip-extra-class' => 'ibexa-tooltip--info-neon',
            ],
            'extras' => [
                'icon' => 'dashboard-clean',
                'orderNumber' => 35,
            ],
        ]);

        $adminMenu = $menu->addChild(self::ITEM_ADMIN, [
            'attributes' => [
                'data-tooltip-placement' => 'right',
                'data-tooltip-extra-class' => 'ibexa-tooltip--info-neon',
                'class' => 'ibexa-adaptive-items__item--force-show',
            ],
            'extras' => [
                'separate' => true,
                'bottom_item' => true,
                'icon' => 'settings-block',
                'orderNumber' => 140,
            ],
        ]);

        if (null !== $token && is_object($token->getUser())) {
            $menu->addChild(self::ITEM_BOOKMARKS, [
                'route' => 'ibexa.bookmark.list',
                'attributes' => [
                    'data-tooltip-placement' => 'right',
                    'data-tooltip-extra-class' => 'ibexa-tooltip--info-neon',
                    'class' => 'ibexa-adaptive-items__item--force-show',
                ],
                'extras' => [
                    'bottom_item' => true,
                    'icon' => 'bookmark',
                    'orderNumber' => 160,
                ],
            ]);
        }

        $menu->addChild(self::ITEM_TRASH, [
            'route' => 'ibexa.trash.list',
            'attributes' => [
                'data-tooltip-placement' => 'right',
                'data-tooltip-extra-class' => 'ibexa-tooltip--info-neon',
                'class' => 'ibexa-adaptive-items__item--force-show',
            ],
            'extras' => [
                'bottom_item' => true,
                'icon' => 'trash',
                'orderNumber' => 180,
            ],
        ]);

        $this->addContentMenuItems($contentMenu);
        $this->addAdminMenuItems($adminMenu);

        return $menu;
    }

    /**
     * @param \Knp\Menu\ItemInterface $menu
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function addContentMenuItems(ItemInterface $menu): void
    {
        $rootContentId = $this->configResolver->getParameter('location_ids.content_structure');
        $rootMediaId = $this->configResolver->getParameter('location_ids.media');

        $contentStructureItem = $this->factory->createLocationMenuItem(
            self::ITEM_CONTENT__CONTENT_STRUCTURE,
            $rootContentId,
            [
                'label' => self::ITEM_CONTENT__CONTENT_STRUCTURE,
                'extras' => [
                    'orderNumber' => 25,
                ],
            ]
        );

        $mediaItem = $this->factory->createLocationMenuItem(
            self::ITEM_CONTENT__MEDIA,
            $rootMediaId,
            [
                'label' => self::ITEM_CONTENT__MEDIA,
                'extras' => [
                    'orderNumber' => 35,
                ],
            ]
        );

        $contentGroupSettings = $menu->addChild(
            self::ITEM_CONTENT_GROUP_SETTINGS,
            [
                'extras' => [
                    'orderNumber' => 75,
                ],
            ],
        );

        if ($this->permissionResolver->hasAccess('section', 'view') !== false) {
            $contentGroupSettings->addChild(
                self::ITEM_ADMIN__SECTIONS,
                self::ITEM_ADMIN_OPTIONS[self::ITEM_ADMIN__SECTIONS]
            );
        }

        $contentGroupSettings->addChild(
            self::ITEM_ADMIN__CONTENT_TYPES,
            self::ITEM_ADMIN_OPTIONS[self::ITEM_ADMIN__CONTENT_TYPES]
        );

        if ($this->permissionResolver->hasAccess('state', 'administrate')) {
            $contentGroupSettings->addChild(
                self::ITEM_ADMIN__OBJECT_STATES,
                self::ITEM_ADMIN_OPTIONS[self::ITEM_ADMIN__OBJECT_STATES]
            );
        }

        if (null !== $contentStructureItem) {
            $menu->addChild($contentStructureItem);
        }

        if (null !== $mediaItem) {
            $menu->addChild($mediaItem);
        }
    }

    /**
     * @param \Knp\Menu\ItemInterface $menu
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function addAdminMenuItems(ItemInterface $menu): void
    {
        if ($this->permissionResolver->hasAccess('role', 'read')) {
            $menu->addChild(
                self::ITEM_ADMIN__ROLES,
                self::ITEM_ADMIN_OPTIONS[self::ITEM_ADMIN__ROLES]
            );
        }
        if ($this->permissionResolver->hasAccess('setup', 'administrate')) {
            $menu->addChild(
                self::ITEM_ADMIN__LANGUAGES,
                self::ITEM_ADMIN_OPTIONS[self::ITEM_ADMIN__LANGUAGES]
            );
        }

        $rootUsersId = $this->configResolver->getParameter('location_ids.users');
        $usersItem = $this->factory->createLocationMenuItem(
            self::ITEM_ADMIN__USERS,
            $rootUsersId,
            [
                'label' => self::ITEM_ADMIN__USERS,
                'extras' => [
                    'orderNumber' => 10,
                ],
            ]
        );

        if (null !== $usersItem) {
            $menu->addChild($usersItem);
        }

        $menu->addChild(
            self::ITEM_ADMIN__URL_MANAGEMENT,
            self::ITEM_ADMIN_OPTIONS[self::ITEM_ADMIN__URL_MANAGEMENT]
        );
    }

    /**
     * @return array
     */
    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::ITEM_DASHBOARD, 'menu'))->setDesc('Dashboard'),
            (new Message(self::ITEM_DRAFTS, 'menu'))->setDesc('Drafts'),
            (new Message(self::ITEM_BOOKMARKS, 'menu'))->setDesc('Bookmarks'),
            (new Message(self::ITEM_TRASH, 'menu'))->setDesc('Trash'),
            (new Message(self::ITEM_CONTENT, 'menu'))->setDesc('Content'),
            (new Message(self::ITEM_CONTENT_GROUP_SETTINGS, 'menu'))->setDesc('Settings'),
            (new Message(self::ITEM_CONTENT__CONTENT_STRUCTURE, 'menu'))->setDesc('Content structure'),
            (new Message(self::ITEM_CONTENT__MEDIA, 'menu'))->setDesc('Media'),
            (new Message(self::ITEM_ADMIN, 'menu'))->setDesc('Admin'),
            (new Message(self::ITEM_ADMIN__SECTIONS, 'menu'))->setDesc('Sections'),
            (new Message(self::ITEM_ADMIN__ROLES, 'menu'))->setDesc('Roles'),
            (new Message(self::ITEM_ADMIN__LANGUAGES, 'menu'))->setDesc('Languages'),
            (new Message(self::ITEM_ADMIN__CONTENT_TYPES, 'menu'))->setDesc('Content Types'),
            (new Message(self::ITEM_ADMIN__USERS, 'menu'))->setDesc('Users'),
            (new Message(self::ITEM_ADMIN__OBJECT_STATES, 'menu'))->setDesc('Object States'),
            (new Message(self::ITEM_ADMIN__URL_MANAGEMENT, 'menu'))->setDesc('URL management'),
        ];
    }
}

class_alias(MainMenuBuilder::class, 'EzSystems\EzPlatformAdminUi\Menu\MainMenuBuilder');
