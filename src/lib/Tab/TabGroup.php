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
    protected string $identifier;

    /** @var \Ibexa\Contracts\AdminUi\Tab\TabInterface[] */
    protected $tabs;

    /**
     * @param string $name
     * @param array $tabs
     */
    public function __construct(string $name, array $tabs = [])
    {
        $this->identifier = $name;
        $this->tabs = $tabs;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
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

    /**
     * @param \Ibexa\Contracts\AdminUi\Tab\TabInterface $tab
     */
    public function addTab(TabInterface $tab): void
    {
        $this->tabs[$tab->getIdentifier()] = $tab;
    }

    /**
     * @param string $identifier
     */
    public function removeTab(string $identifier): void
    {
        if (!isset($this->tabs[$identifier])) {
            throw new InvalidArgumentException(sprintf('Could not find a tab identified as "%s".', $identifier));
        }

        unset($this->tabs[$identifier]);
    }
}
