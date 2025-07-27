<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Event;

use Ibexa\AdminUi\Tab\TabGroup;
use Symfony\Contracts\EventDispatcher\Event;

class TabGroupEvent extends Event
{
    private TabGroup $data;

    /** @var array<string, mixed> */
    private array $parameters = [];

    public function getData(): TabGroup
    {
        return $this->data;
    }

    public function setData(TabGroup $data): void
    {
        $this->data = $data;
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
