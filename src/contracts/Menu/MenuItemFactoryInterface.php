<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\AdminUi\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

interface MenuItemFactoryInterface extends FactoryInterface
{
    /**
     * Creates Location menu item only when user has content:read permission.
     *
     * @param array<mixed> $options
     *
     * @return \Knp\Menu\ItemInterface|null
     */
    public function createLocationMenuItem(string $name, int $locationId, array $options = []): ?ItemInterface;
}
