<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Component\Event;

use Ibexa\AdminUi\Component\Registry;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @deprecated 4.6.19 The {@see \Ibexa\AdminUi\Component\Event\RenderGroupEvent} class is deprecated, will be removed in 5.0.
 * Use {@see \Ibexa\Contracts\TwigComponents\Event\RenderGroupEvent} instead
 */
class RenderGroupEvent extends Event
{
    public const NAME = 'ezplatform_admin_ui.component.render_group';

    private Registry $registry;

    private string $groupName;

    private array $parameters;

    /**
     * @param \Ibexa\AdminUi\Component\Registry $registry
     * @param string $groupName
     * @param array $parameters
     */
    public function __construct(Registry $registry, string $groupName, array $parameters = [])
    {
        $this->registry = $registry;
        $this->groupName = $groupName;
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
     * @return array
     */
    public function getComponents(): array
    {
        return $this->registry->getComponents($this->getGroupName());
    }

    /**
     * @param array $components
     */
    public function setComponents(array $components): void
    {
        $this->registry->setComponents($this->getGroupName(), $components);
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
