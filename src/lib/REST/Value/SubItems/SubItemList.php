<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\SubItems;

use Ibexa\Rest\Value;

final class SubItemList extends Value
{
    /**
     * @param SubItem[] $elements
     */
    public function __construct(
        public readonly int $totalCount,
        public readonly array $elements = []
    ) {
    }
}
