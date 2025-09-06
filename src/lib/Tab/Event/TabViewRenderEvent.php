<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class TabViewRenderEvent extends Event
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        private readonly string $tabIdentifier,
        private string $template,
        private array $parameters = []
    ) {
    }

    public function getTabIdentifier(): string
    {
        return $this->tabIdentifier;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }
}
