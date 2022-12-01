<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Menu;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\AdminUi\Menu\MenuItemFactory;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Base builder for extendable AdminUI menus.
 */
abstract class AbstractBuilder
{
    /** @var \Ibexa\AdminUi\Menu\MenuItemFactory */
    protected $factory;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param \Ibexa\AdminUi\Menu\MenuItemFactory $factory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(MenuItemFactory $factory, EventDispatcherInterface $eventDispatcher)
    {
        $this->factory = $factory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $id
     * @param array $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function createMenuItem(string $id, array $options = []): ItemInterface
    {
        return $this->factory->createItem($id, $options);
    }

    /**
     * @param string $name
     * @param \Symfony\Contracts\EventDispatcher\Event $event
     */
    protected function dispatchMenuEvent(string $name, Event $event): void
    {
        $this->eventDispatcher->dispatch($event, $name);
    }

    /**
     * @param \Knp\Menu\ItemInterface $menu
     *
     * @return \Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent
     */
    protected function createConfigureMenuEvent(ItemInterface $menu, array $options = []): ConfigureMenuEvent
    {
        return new ConfigureMenuEvent($this->factory, $menu, $options);
    }

    /**
     * @param array $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function build(array $options): ItemInterface
    {
        $menu = $this->createStructure($options);

        $this->dispatchMenuEvent($this->getConfigureEventName(), $this->createConfigureMenuEvent($menu, $options));

        return $menu;
    }

    abstract protected function getConfigureEventName(): string;

    abstract protected function createStructure(array $options): ItemInterface;
}

class_alias(AbstractBuilder::class, 'EzSystems\EzPlatformAdminUi\Menu\AbstractBuilder');
