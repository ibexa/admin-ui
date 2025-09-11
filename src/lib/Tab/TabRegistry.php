<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Tab;

use Ibexa\Contracts\AdminUi\Tab\TabInterface;
use InvalidArgumentException;

class TabRegistry
{
    /** @var \Ibexa\AdminUi\Tab\TabGroup[] */
    private array $tabGroups = [];

    /**
     * @return \Ibexa\Contracts\AdminUi\Tab\TabInterface[]
     */
    public function getTabsByGroupName(string $group): array
    {
        return $this->getTabGroup($group)->getTabs();
    }

    public function getTabGroup(string $group): TabGroup
    {
        if (!isset($this->tabGroups[$group])) {
            throw new InvalidArgumentException(
                sprintf('Could not find the requested group named "%s". Did you tag the service?', $group)
            );
        }

        return $this->tabGroups[$group];
    }

    public function getTabFromGroup(string $name, string $group): TabInterface
    {
        if (!isset($this->tabGroups[$group])) {
            throw new InvalidArgumentException(
                sprintf('Could not find the requested group named "%s". Did you tag the service?', $group)
            );
        }

        foreach ($this->tabGroups[$group]->getTabs() as $tab) {
            if ($tab->getName() === $name) {
                return $tab;
            }
        }

        throw new InvalidArgumentException(
            sprintf('Could not find the requested tab "%s" from group "%s". Did you tag the service?', $name, $group)
        );
    }

    public function addTabGroup(TabGroup $group): void
    {
        $this->tabGroups[$group->getIdentifier()] = $group;
    }

    public function addTab(TabInterface $tab, string $group): void
    {
        if (!isset($this->tabGroups[$group])) {
            $this->tabGroups[$group] = new TabGroup($group, []);
        }

        $this->tabGroups[$group]->addTab($tab);
    }
}
