<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab\Event;

use Ibexa\Contracts\AdminUi\Tab\TabInterface;
use Symfony\Contracts\EventDispatcher\Event;

class TabEvent extends Event
{
    /** @var \Ibexa\Contracts\AdminUi\Tab\TabInterface */
    private TabInterface $data;

    /** @var array<string, mixed> */
    private array $parameters = [];

    /**
     * @return \Ibexa\Contracts\AdminUi\Tab\TabInterface
     */
    public function getData(): TabInterface
    {
        return $this->data;
    }

    /**
     * @param \Ibexa\Contracts\AdminUi\Tab\TabInterface $data
     */
    public function setData(TabInterface $data): void
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
