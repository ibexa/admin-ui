<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Component;

use Ibexa\AdminUi\Tab\Event\TabEvent;
use Ibexa\AdminUi\Tab\Event\TabEvents;
use Ibexa\AdminUi\Tab\Event\TabGroupEvent;
use Ibexa\AdminUi\Tab\TabGroup;
use Ibexa\Contracts\AdminUi\Tab\TabInterface;
use Ibexa\Contracts\TwigComponents\ComponentInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

readonly class TabsComponent implements ComponentInterface
{
    /**
     * @param array<mixed> $parameters
     */
    public function __construct(
        protected Environment $twig,
        protected EventDispatcherInterface $eventDispatcher,
        protected string $template,
        protected string $groupIdentifier,
        protected array $parameters = []
    ) {
    }

    /**
     * @param array<mixed> $parameters
     */
    public function render(array $parameters = []): string
    {
        $tabGroup = new TabGroup($this->groupIdentifier);

        $tabGroupEvent = new TabGroupEvent();
        $tabGroupEvent->setData($tabGroup);
        $tabGroupEvent->setParameters($parameters);

        $this->eventDispatcher->dispatch($tabGroupEvent, TabEvents::TAB_GROUP_INITIALIZE);

        $this->eventDispatcher->dispatch($tabGroupEvent, TabEvents::TAB_GROUP_PRE_RENDER);

        $tabs = [];
        foreach ($tabGroupEvent->getData()->getTabs() as $tab) {
            $tabEvent = $this->dispatchTabPreRenderEvent($tab, $parameters);
            $parameters = array_merge($parameters, $tabGroupEvent->getParameters(), $tabEvent->getParameters());
            $tabs[] = $this->composeTabParameters($tabEvent->getData(), $parameters);
        }

        return $this->twig->render(
            $this->template,
            array_merge($this->parameters, $parameters, ['tabs' => $tabs, 'group' => $this->groupIdentifier])
        );
    }

    /**
     * @param array<mixed> $parameters
     */
    private function dispatchTabPreRenderEvent(TabInterface $tab, array $parameters): TabEvent
    {
        $tabEvent = new TabEvent();
        $tabEvent->setData($tab);
        $tabEvent->setParameters($parameters);

        $this->eventDispatcher->dispatch($tabEvent, TabEvents::TAB_PRE_RENDER);

        return $tabEvent;
    }

    /**
     * @param array<mixed> $parameters
     *
     * @return array{name: string, view: string, identifier: string}
     */
    private function composeTabParameters(TabInterface $tab, array $parameters): array
    {
        return [
            'name' => $tab->getName(),
            'view' => $tab->renderView($parameters),
            'identifier' => $tab->getIdentifier(),
        ];
    }
}
