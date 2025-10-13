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
 * @deprecated 4.6.19 The {@see \Ibexa\AdminUi\Component\Event\RenderSingleEvent} class is deprecated, will be removed in 6.0.
 * Use {@see \Ibexa\Contracts\TwigComponents\Event\RenderSingleEvent} instead
 */
final class RenderSingleEvent extends Event
{
    public const string NAME = 'ezplatform_admin_ui.component.render_single';

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        private readonly Registry $registry,
        private readonly string $groupName,
        private readonly string $serviceId,
        private readonly array $parameters = []
    ) {
    }

    public function getGroupName(): string
    {
        return $this->groupName;
    }

    public function getName(): string
    {
        return $this->serviceId;
    }

    public function getComponent(): ComponentInterface
    {
        $group = $this->registry->getComponents($this->getGroupName());

        return $group[$this->serviceId];
    }

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
