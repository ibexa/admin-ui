<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Action;

use Ibexa\Contracts\AdminUi\UI\Action\EventDispatcherInterface;
use Ibexa\Contracts\AdminUi\UI\Action\UiActionEventInterface;
use Symfony\Component\EventDispatcher as SymfonyEventDispatcher;

class EventDispatcher implements EventDispatcherInterface
{
    protected SymfonyEventDispatcher\EventDispatcherInterface $eventDispatcher;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(SymfonyEventDispatcher\EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param \Ibexa\Contracts\AdminUi\UI\Action\UiActionEventInterface $event
     */
    public function dispatch(UiActionEventInterface $event): void
    {
        $action = $event->getName();

        $this->eventDispatcher->dispatch($event, EventDispatcherInterface::EVENT_NAME_PREFIX);
        $this->eventDispatcher->dispatch($event, sprintf('%s.%s', EventDispatcherInterface::EVENT_NAME_PREFIX, $action));
        $this->eventDispatcher->dispatch($event, sprintf('%s.%s.%s', EventDispatcherInterface::EVENT_NAME_PREFIX, $action, $event->getType()));
    }
}
