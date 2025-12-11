<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Menu;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Base builder for extendable AdminUI menus.
 */
abstract class AbstractBuilder
{
    public function __construct(
        protected readonly MenuItemFactoryInterface $factory,
        protected readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @param array<mixed> $options
     */
    protected function createMenuItem(string $id, array $options = []): ItemInterface
    {
        return $this->factory->createItem($id, $options);
    }

    protected function dispatchMenuEvent(string $name, Event $event): void
    {
        $this->eventDispatcher->dispatch($event, $name);
    }

    /**
     * @param array<mixed> $options
     */
    protected function createConfigureMenuEvent(ItemInterface $menu, array $options = []): ConfigureMenuEvent
    {
        return new ConfigureMenuEvent($this->factory, $menu, $options);
    }

    /**
     * @param array<mixed> $options
     */
    public function build(array $options): ItemInterface
    {
        $menu = $this->createStructure($options);

        $this->dispatchMenuEvent($this->getConfigureEventName(), $this->createConfigureMenuEvent($menu, $options));

        return $menu;
    }

    abstract protected function getConfigureEventName(): string;

    /**
     * @param array<mixed> $options
     */
    abstract protected function createStructure(array $options): ItemInterface;
}
