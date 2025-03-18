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
    /** @var \Ibexa\AdminUi\Tab\TabGroup */
    private TabGroup $data;

    private ?array $parameters = null;

    /**
     * @return \Ibexa\AdminUi\Tab\TabGroup
     */
    public function getData(): TabGroup
    {
        return $this->data;
    }

    /**
     * @param \Ibexa\AdminUi\Tab\TabGroup $data
     */
    public function setData(TabGroup $data): void
    {
        $this->data = $data;
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
