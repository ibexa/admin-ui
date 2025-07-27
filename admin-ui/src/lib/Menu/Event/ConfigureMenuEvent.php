<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu\Event;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event triggered after building AdminUI menus. Provides extensibility point for menus' customization.
 */
class ConfigureMenuEvent extends Event
{
    public const MAIN_MENU = 'ezplatform_admin_ui.menu_configure.main_menu';
    public const USER_MENU = 'ezplatform_admin_ui.menu_configure.user_menu';
    public const CONTENT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.content_sidebar_right';
    public const CONTENT_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.content_edit_sidebar_right';
    public const CONTENT_EDIT_ANCHOR_MENU = 'ibexa.admin_ui.menu_configure.content_edit_anchor_menu';
    public const CONTENT_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.content_create_sidebar_right';
    public const TRASH_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.trash_sidebar_right';
    public const SECTION_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.section_edit_sidebar_right';
    public const SECTION_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.section_create_sidebar_right';
    public const POLICY_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.policy_edit_sidebar_right';
    public const POLICY_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.policy_create_sidebar_right';
    public const ROLE_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.role_edit_sidebar_right';
    public const ROLE_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.role_create_sidebar_right';
    public const ROLE_COPY_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.role_copy_sidebar_right';
    public const USER_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.user_edit_sidebar_right';
    public const USER_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.user_create_sidebar_right';
    public const ROLE_ASSIGNMENT_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.role_assignment_create_sidebar_right';
    public const LANGUAGE_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.language_create_sidebar_right';
    public const LANGUAGE_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.language_edit_sidebar_right';
    public const CONTENT_TYPE_GROUP_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.content_type_group_create_sidebar_right';
    public const CONTENT_TYPE_GROUP_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.content_type_group_edit_sidebar_right';
    public const CONTENT_TYPE_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.content_type_create_sidebar_right';
    public const CONTENT_TYPE_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.content_type_edit_sidebar_right';
    public const CONTENT_TYPE_EDIT_ANCHOR_MENU = 'ibexa.admin_ui.menu_configure.content_type_edit_anchor_menu';
    public const URL_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.url_edit_sidebar_right';
    public const URL_WILDCARD_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.url_wildcard_edit_sidebar_right';
    public const USER_PASSWORD_CHANGE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.user_password_change_sidebar_right';
    public const OBJECT_STATE_GROUP_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.object_state_group_create_sidebar_right';
    public const OBJECT_STATE_GROUP_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.object_state_group_edit_sidebar_right';
    public const OBJECT_STATE_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.object_state_create_sidebar_right';
    public const OBJECT_STATE_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.object_state_edit_sidebar_right';
    public const USER_SETTING_UPDATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.user_setting_update_sidebar_right';
    public const CONTENT_TYPE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.content_type_sidebar_right';

    private FactoryInterface $factory;

    private ItemInterface $menu;

    /** @var array<string, mixed> */
    private array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(FactoryInterface $factory, ItemInterface $menu, array $options = [])
    {
        $this->factory = $factory;
        $this->menu = $menu;
        $this->options = $options;
    }

    public function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    public function getMenu(): ItemInterface
    {
        return $this->menu;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
