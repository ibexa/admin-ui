<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Component\EventSubscriber;

use Ibexa\AdminUi\Component\Event\RenderGroupEvent;
use Ibexa\AdminUi\Component\Event\RenderSingleEvent;
use Ibexa\AdminUi\Component\Registry;
use Ibexa\Contracts\TwigComponents\Event\RenderGroupEvent as TwigComponentsRenderGroupEvent;
use Ibexa\Contracts\TwigComponents\Event\RenderSingleEvent as TwigComponentsRenderSingleEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class RenderEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private Registry $registry
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TwigComponentsRenderGroupEvent::class => ['onRenderGroup', 500],
            TwigComponentsRenderSingleEvent::class => ['onRenderSingle', 500],
        ];
    }

    public function onRenderGroup(TwigComponentsRenderGroupEvent $event): void
    {
        $this->eventDispatcher->dispatch(new RenderGroupEvent(
            $this->registry,
            $event->getGroupName(),
            $event->getParameters(),
        ), RenderGroupEvent::NAME);
    }

    public function onRenderSingle(TwigComponentsRenderSingleEvent $event): void
    {
        $this->eventDispatcher->dispatch(new RenderSingleEvent(
            $this->registry,
            $event->getGroupName(),
            $event->getName(),
            $event->getParameters(),
        ), RenderSingleEvent::NAME);
    }
}
