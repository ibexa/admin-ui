<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\AdminUi\Menu\MainMenuBuilder;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MenuPermissionsListener implements EventSubscriberInterface
{
    private PermissionResolver $permissionResolver;

    /**
     * @param \Ibexa\Contracts\Core\Repository\PermissionResolver $permissionResolver
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
     * @param \Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent $event
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function checkPermissions(ConfigureMenuEvent $event): void
    {
        if (!$this->permissionResolver->hasAccess('setup', 'administrate')) {
            $menu = $event->getMenu();
            $menu->removeChild(MainMenuBuilder::ITEM_ADMIN);
        }
    }
}
