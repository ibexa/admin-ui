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
 * @deprecated 4.6.19 The {@see \Ibexa\AdminUi\Component\Event\RenderGroupEvent} class is deprecated, will be removed in 6.0.
 * Use {@see \Ibexa\Contracts\TwigComponents\Event\RenderGroupEvent} instead
 */
final class RenderGroupEvent extends Event
{
    public const string NAME = 'ezplatform_admin_ui.component.render_group';

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        private readonly Registry $registry,
        private readonly string $groupName,
        private readonly array $parameters = []
    ) {
    }

    public function getGroupName(): string
    {
        return $this->groupName;
    }

    /**
     * @return \Ibexa\Contracts\TwigComponents\ComponentInterface[]
     */
    public function getComponents(): array
    {
        return $this->registry->getComponents($this->getGroupName());
    }

    /**
     * @param \Ibexa\Contracts\TwigComponents\ComponentInterface[] $components
     */
    public function setComponents(array $components): void
    {
        $this->registry->setComponents($this->getGroupName(), $components);
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
