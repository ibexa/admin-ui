<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Service;

use Ibexa\AdminUi\Tab\TabGroup;
use Ibexa\AdminUi\Tab\TabRegistry;
use Ibexa\Contracts\AdminUi\Tab\TabInterface;
use InvalidArgumentException;

final readonly class TabService
{
    public function __construct(private TabRegistry $tabRegistry)
    {
    }

    public function getTabGroup(string $groupIdentifier): TabGroup
    {
        return $this->tabRegistry->getTabGroup($groupIdentifier);
    }

    /**
     * @return \Ibexa\Contracts\AdminUi\Tab\TabInterface[]
     */
    public function getTabsFromGroup(string $groupIdentifier): array
    {
        $tabGroup = $this->tabRegistry->getTabGroup($groupIdentifier);

        return $tabGroup->getTabs();
    }

    public function getTabFromGroup(string $tabIdentifier, string $groupIdentifier): TabInterface
    {
        $tabs = $this->getTabsFromGroup($groupIdentifier);

        if (!isset($tabs[$tabIdentifier])) {
            throw new InvalidArgumentException(sprintf(
                'There is no "%s" tab assigned to "%s" group.',
                $tabIdentifier,
                $groupIdentifier
            ));
        }

        return $tabs[$tabIdentifier];
    }
}
