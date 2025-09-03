<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu\Admin;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\AdminUi\Menu\MainMenuBuilder;
use Knp\Menu\Util\MenuManipulator;

final readonly class ReorderMenuListener
{
    public function moveAdminToLast(ConfigureMenuEvent $event): void
    {
        $menu = $event->getMenu();
        $adminChild = $menu->getChild(MainMenuBuilder::ITEM_ADMIN);
        if (!$adminChild) {
            return;
        }

        $manipulator = new MenuManipulator();
        $manipulator->moveToLastPosition($adminChild);
    }
}
