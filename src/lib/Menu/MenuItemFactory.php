<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu;

use Exception;
use Ibexa\Contracts\AdminUi\Menu\MenuItemFactoryInterface;
use Ibexa\Contracts\Core\Repository\LocationService;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

readonly class MenuItemFactory implements MenuItemFactoryInterface
{
    public function __construct(
        protected FactoryInterface $factory,
        private LocationService $locationService
    ) {
    }

    /**
     * Creates Location menu item only when user has content:read permission.
     *
     * @param array<string, mixed> $options
     */
    public function createLocationMenuItem(string $name, int $locationId, array $options = []): ?ItemInterface
    {
        try {
            $location = $this->locationService->loadLocation($locationId);
            $contentInfo = $location->getContentInfo();
        } catch (Exception) {
            return null;
        }

        $defaults = [
            'route' => 'ibexa.content.view',
            'routeParameters' => [
                'contentId' => $contentInfo->getId(),
                'locationId' => $locationId,
            ],
        ];

        return $this->createItem($name, array_merge_recursive($defaults, $options));
    }

    public function createItem(string $name, array $options = []): ItemInterface
    {
        if (empty($options['extras']['translation_domain'])) {
            $options['extras']['translation_domain'] = 'ibexa_menu';
        }

        $item = $this->factory->createItem($name, $options);
        $item->setFactory($this);

        return $item;
    }
}
