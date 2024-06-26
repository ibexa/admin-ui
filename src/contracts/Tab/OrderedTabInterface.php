<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Tab;

/**
 * Ordered Tab interface needs to be implemented
 * by tabs, which needs to have specific order
 * when being rendered.
 */
interface OrderedTabInterface
{
    /**
     * Get the order of this tab.
     */
    public function getOrder(): int;
}
