<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Component\Event;

use Ibexa\AdminUi\Component\Registry;
use Ibexa\Contracts\AdminUi\Component\Renderable;
use Symfony\Contracts\EventDispatcher\Event;

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
     * @return \Ibexa\Contracts\AdminUi\Component\Renderable
     */
    public function getComponent(): Renderable
    {
        $group = $this->registry->getComponents($this->getGroupName());

        return $group[$this->serviceId];
    }

    /**
     * @param \Ibexa\Contracts\AdminUi\Component\Renderable $component
     */
    public function setComponent(Renderable $component): void
    {
        $this->registry->addComponent($this->getGroupName(), $this->getName(), $component);
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
