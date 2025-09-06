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
final class ConfigureMenuEvent extends Event
{
    public const string MAIN_MENU = 'ezplatform_admin_ui.menu_configure.main_menu';
    public const string USER_MENU = 'ezplatform_admin_ui.menu_configure.user_menu';
    public const string CONTENT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.content_sidebar_right';
    public const string CONTENT_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.content_edit_sidebar_right';
    public const string CONTENT_EDIT_ANCHOR_MENU = 'ibexa.admin_ui.menu_configure.content_edit_anchor_menu';
    public const string CONTENT_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.content_create_sidebar_right';
    public const string TRASH_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.trash_sidebar_right';
    public const string SECTION_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.section_edit_sidebar_right';
    public const string SECTION_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.section_create_sidebar_right';
    public const string POLICY_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.policy_edit_sidebar_right';
    public const string POLICY_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.policy_create_sidebar_right';
    public const string ROLE_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.role_edit_sidebar_right';
    public const string ROLE_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.role_create_sidebar_right';
    public const string ROLE_COPY_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.role_copy_sidebar_right';
    public const string USER_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.user_edit_sidebar_right';
    public const string USER_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.user_create_sidebar_right';
    public const string ROLE_ASSIGNMENT_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.role_assignment_create_sidebar_right';
    public const string LANGUAGE_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.language_create_sidebar_right';
    public const string LANGUAGE_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.language_edit_sidebar_right';
    public const string CONTENT_TYPE_GROUP_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.content_type_group_create_sidebar_right';
    public const string CONTENT_TYPE_GROUP_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.content_type_group_edit_sidebar_right';
    public const string CONTENT_TYPE_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.content_type_create_sidebar_right';
    public const string CONTENT_TYPE_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.content_type_edit_sidebar_right';
    public const string CONTENT_TYPE_EDIT_ANCHOR_MENU = 'ibexa.admin_ui.menu_configure.content_type_edit_anchor_menu';
    public const string URL_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.url_edit_sidebar_right';
    public const string URL_WILDCARD_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.url_wildcard_edit_sidebar_right';
    public const string USER_PASSWORD_CHANGE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.user_password_change_sidebar_right';
    public const string OBJECT_STATE_GROUP_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.object_state_group_create_sidebar_right';
    public const string OBJECT_STATE_GROUP_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.object_state_group_edit_sidebar_right';
    public const string OBJECT_STATE_CREATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.object_state_create_sidebar_right';
    public const string OBJECT_STATE_EDIT_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.object_state_edit_sidebar_right';
    public const string USER_SETTING_UPDATE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.user_setting_update_sidebar_right';
    public const string CONTENT_TYPE_SIDEBAR_RIGHT = 'ezplatform_admin_ui.menu_configure.content_type_sidebar_right';

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly ItemInterface $menu,
        private readonly array $options = []
    ) {
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
