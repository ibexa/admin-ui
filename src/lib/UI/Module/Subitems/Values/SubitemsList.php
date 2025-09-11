<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Module\Subitems\Values;

use Ibexa\Rest\Value as RestValue;

final class SubitemsList extends RestValue
{
    /**
     * @param \Ibexa\AdminUi\UI\Module\Subitems\Values\SubitemsRow[] $subitemRows
     */
    public function __construct(
        public readonly array $subitemRows,
        public readonly int $childrenCount
    ) {
    }
}
