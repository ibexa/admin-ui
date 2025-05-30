<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Event;

use Symfony\Contracts\EventDispatcher\Event;

class TabViewRenderEvent extends Event
{
    private string $tabIdentifier;

    private string $template;

    private array $parameters;

    /**
     * @param string $tabIdentifier
     * @param string $template
     * @param array $parameters
     */
    public function __construct(string $tabIdentifier, string $template, array $parameters = [])
    {
        $this->tabIdentifier = $tabIdentifier;
        $this->template = $template;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getTabIdentifier(): string
    {
        return $this->tabIdentifier;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }
}
