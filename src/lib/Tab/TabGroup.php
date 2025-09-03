<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab;

use Ibexa\Contracts\AdminUi\Tab\TabInterface;
use InvalidArgumentException;

class TabGroup
{
    /**
     * @param \Ibexa\Contracts\AdminUi\Tab\TabInterface[] $tabs
     */
    public function __construct(
        protected string $identifier,
        protected array $tabs = []
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @return \Ibexa\Contracts\AdminUi\Tab\TabInterface[]
     */
    public function getTabs(): array
    {
        return $this->tabs;
    }

    /**
     * @param \Ibexa\Contracts\AdminUi\Tab\TabInterface[] $tabs
     */
    public function setTabs(array $tabs): void
    {
        $this->tabs = $tabs;
    }

    public function addTab(TabInterface $tab): void
    {
        $this->tabs[$tab->getIdentifier()] = $tab;
    }

    public function removeTab(string $identifier): void
    {
        if (!isset($this->tabs[$identifier])) {
            throw new InvalidArgumentException(
                sprintf('Could not find a tab identified as "%s".', $identifier)
            );
        }

        unset($this->tabs[$identifier]);
    }
}
