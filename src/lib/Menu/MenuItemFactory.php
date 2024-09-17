<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Menu;

use Ibexa\Contracts\AdminUi\Menu\MenuItemFactoryInterface;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class MenuItemFactory implements MenuItemFactoryInterface
{
    /** @var \Knp\Menu\FactoryInterface */
    protected $factory;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $locationService;

    /**
     * @param \Knp\Menu\FactoryInterface $factory
     * @param \Ibexa\Contracts\Core\Repository\PermissionResolver $permissionResolver
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     */
    public function __construct(
        FactoryInterface $factory,
        PermissionResolver $permissionResolver,
        LocationService $locationService
    ) {
        $this->factory = $factory;
        $this->permissionResolver = $permissionResolver;
        $this->locationService = $locationService;
    }

    /**
     * Creates Location menu item only when user has content:read permission.
     *
     * @param string $name
     * @param int $locationId
     * @param array $options
     *
     * @return \Knp\Menu\ItemInterface|null
     */
    public function createLocationMenuItem(string $name, int $locationId, array $options = []): ?ItemInterface
    {
        try {
            $location = $this->locationService->loadLocation($locationId);
            $contentInfo = $location->getContentInfo();
            $canRead = $this->permissionResolver->canUser('content', 'read', $contentInfo);
        } catch (\Exception $e) {
            return null;
        }

        $defaults = [
            'route' => 'ibexa.content.view',
            'routeParameters' => [
                'contentId' => $contentInfo->id,
                'locationId' => $locationId,
            ],
        ];

        return $this->createItem($name, array_merge_recursive($defaults, $options));
    }

    public function createItem($name, array $options = []): ItemInterface
    {
        if (empty($options['extras']['translation_domain'])) {
            $options['extras']['translation_domain'] = 'ibexa_menu';
        }

        $item = $this->factory->createItem($name, $options);
        $item->setFactory($this);

        return $item;
    }
}

class_alias(MenuItemFactory::class, 'EzSystems\EzPlatformAdminUi\Menu\MenuItemFactory');
