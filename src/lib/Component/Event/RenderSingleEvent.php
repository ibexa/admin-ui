<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Component\Event;

use Ibexa\AdminUi\Component\Registry;
use Ibexa\Contracts\TwigComponents\ComponentInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @deprecated 4.6.19 The {@see \Ibexa\AdminUi\Component\Event\RenderSingleEvent} class is deprecated, will be removed in 5.0.
 * Use {@see \Ibexa\Contracts\TwigComponents\Event\RenderSingleEvent} instead
 */
class RenderSingleEvent extends Event
{
    public const NAME = 'ezplatform_admin_ui.component.render_single';

    private Registry $registry;

    private string $groupName;

    private string $serviceId;

    private array $parameters;

    /**
     * @param \Ibexa\AdminUi\Component\Registry $registry
     * @param string $groupName
     * @param array $parameters
     */
    public function __construct(Registry $registry, string $groupName, string $serviceId, array $parameters = [])
    {
        $this->registry = $registry;
        $this->groupName = $groupName;
        $this->serviceId = $serviceId;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getGroupName(): string
    {
        return $this->groupName;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->serviceId;
    }

    /**
     * @return \Ibexa\Contracts\TwigComponents\ComponentInterface
     */
    public function getComponent(): ComponentInterface
    {
        $group = $this->registry->getComponents($this->getGroupName());

        return $group[$this->serviceId];
    }

    /**
     * @param \Ibexa\Contracts\TwigComponents\ComponentInterface $component
     */
    public function setComponent(ComponentInterface $component): void
    {
        $this->registry->addComponent($this->getGroupName(), $this->getName(), $component);
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
