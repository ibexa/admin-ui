<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Menu;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\AdminUi\UniversalDiscovery\ConfigResolver;
use Ibexa\Bundle\AdminUi\Templating\Twig\UniversalDiscoveryExtension;
use Ibexa\Contracts\AdminUi\Menu\AbstractBuilder;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * KnpMenuBundle Menu Builder service implementation for AdminUI left sidebar menu.
 *
 * @see https://symfony.com/doc/current/bundles/KnpMenuBundle/menu_builder_service.html
 */
class LeftSidebarBuilder extends AbstractBuilder implements TranslationContainerInterface
{
    /* Menu items */
    public const ITEM__SEARCH = 'sidebar_left__search';
    public const ITEM__BROWSE = 'sidebar_left__browse';
    public const ITEM__BOOKMARK = 'sidebar_left__bookmark';
    public const ITEM__TRASH = 'sidebar_left__trash';
    public const ITEM__TREE = 'sidebar_left__tree';

    /** @var \Ibexa\AdminUi\UniversalDiscovery\ConfigResolver */
    private $configResolver;

    /** @var \Ibexa\Bundle\AdminUi\Templating\Twig\UniversalDiscoveryExtension */
    private $udwExtension;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    public function __construct(
        MenuItemFactory $factory,
        EventDispatcherInterface $eventDispatcher,
        ConfigResolver $configResolver,
        UniversalDiscoveryExtension $udwExtension,
        PermissionResolver $permissionResolver,
        TranslatorInterface $translator
    ) {
        parent::__construct($factory, $eventDispatcher);

        $this->configResolver = $configResolver;
        $this->udwExtension = $udwExtension;
        $this->permissionResolver = $permissionResolver;
        $this->translator = $translator;
    }

    /**
     * @return string
     */
    protected function getConfigureEventName(): string
    {
        return ConfigureMenuEvent::CONTENT_SIDEBAR_LEFT;
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
        $menu = $this->factory->createItem('root');

        $menuItems = [
            self::ITEM__SEARCH => $this->createMenuItem(
                self::ITEM__SEARCH,
                [
                    'route' => 'ibexa.search',
                    'extras' => ['icon' => 'search'],
                ]
            ),
            self::ITEM__BROWSE => $this->createMenuItem(
                self::ITEM__BROWSE,
                [
                    'extras' => ['icon' => 'browse'],
                    'attributes' => [
                        'type' => 'button',
                        'class' => 'ibexa-btn--udw-browse',
                        'data-udw-config' => $this->udwExtension->renderUniversalDiscoveryWidgetConfig('browse', [
                            'type' => 'content_create',
                        ]),
                        'data-starting-location-id' => $this->configResolver->getConfig('default')['starting_location_id'],
                    ],
                ]
            ),
            self::ITEM__TREE => $this->createMenuItem(
                self::ITEM__TREE,
                [
                    'extras' => ['icon' => 'content-tree'],
                    'attributes' => [
                        'type' => 'button',
                        'class' => 'btn ibexa-btn ibexa-btn--toggle-content-tree',
                    ],
                ]
            ),
            self::ITEM__BOOKMARK => $this->createMenuItem(
                self::ITEM__BOOKMARK,
                [
                    'route' => 'ibexa.bookmark.list',
                    'extras' => ['icon' => 'bookmark-manager'],
                ]
            ),
        ];

        if ($this->permissionResolver->hasAccess('content', 'restore')) {
            $menuItems[self::ITEM__TRASH] = $this->createMenuItem(
                self::ITEM__TRASH,
                [
                    'route' => 'ibexa.trash.list',
                    'extras' => ['icon' => 'trash'],
                ]
            );
        }

        $menu->setChildren($menuItems);

        return $menu;
    }

    /**
     * @return \JMS\TranslationBundle\Model\Message[]
     */
    public static function getTranslationMessages(): array
    {
        return [
            (new Message(self::ITEM__SEARCH, 'ibexa_menu'))->setDesc('Search'),
            (new Message(self::ITEM__BROWSE, 'ibexa_menu'))->setDesc('Browse'),
            (new Message(self::ITEM__TREE, 'ibexa_menu'))->setDesc('Content Tree'),
            (new Message(self::ITEM__TRASH, 'ibexa_menu'))->setDesc('Trash'),
            (new Message(self::ITEM__BOOKMARK, 'ibexa_menu'))->setDesc('Bookmarks'),
        ];
    }
}

class_alias(LeftSidebarBuilder::class, 'EzSystems\EzPlatformAdminUi\Menu\LeftSidebarBuilder');
