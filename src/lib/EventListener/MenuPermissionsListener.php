<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\AdminUi\Menu\MainMenuBuilder;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MenuPermissionsListener implements EventSubscriberInterface
{
    /** @var PermissionResolver */
    private $permissionResolver;

    /**
     * @param PermissionResolver $permissionResolver
     */
    public function __construct(PermissionResolver $permissionResolver)
    {
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [ConfigureMenuEvent::MAIN_MENU => 'checkPermissions'];
    }

    /**
     * @param ConfigureMenuEvent $event
     *
     * @throws InvalidArgumentException
     */
    public function checkPermissions(ConfigureMenuEvent $event): void
    {
        if (!$this->permissionResolver->hasAccess('setup', 'administrate')) {
            $menu = $event->getMenu();
            $menu->removeChild(MainMenuBuilder::ITEM_ADMIN);
        }
    }
}

class_alias(MenuPermissionsListener::class, 'EzSystems\EzPlatformAdminUi\EventListener\MenuPermissionsListener');
