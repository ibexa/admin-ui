<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Tab;

use Ibexa\AdminUi\Tab\Event\TabEvents;
use Ibexa\AdminUi\Tab\Event\TabViewRenderEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Base class representing Tab using EventDispatcher for extensibility.
 *
 * It extends AbstractTab by adding Event Dispatching before rendering view.
 */
abstract class AbstractEventDispatchingTab extends AbstractTab
{
    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($twig, $translator);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function renderView(array $parameters): string
    {
        $event = new TabViewRenderEvent(
            $this->getIdentifier(),
            $this->getTemplate(),
            $this->getTemplateParameters($parameters)
        );
        $this->eventDispatcher->dispatch($event, TabEvents::TAB_RENDER);

        return $this->twig->render(
            $event->getTemplate(),
            $event->getParameters()
        );
    }

    abstract public function getTemplate(): string;

    /**
     * @param array<string, mixed> $contextParameters
     *
     * @return array<string, mixed>
     */
    abstract public function getTemplateParameters(array $contextParameters = []): array;
}
