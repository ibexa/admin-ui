<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\AdminUi\Menu;

use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Knp\Menu\ItemInterface;

interface ContentAwareActionItemFactoryInterface
{
    public function createEditDraftAction(
        string $name,
        VersionInfo $versionInfo,
        bool $isDraftConflict = false,
        ?int $locationId = null,
        ?int $orderNumber = 0
    ): ItemInterface;
}
