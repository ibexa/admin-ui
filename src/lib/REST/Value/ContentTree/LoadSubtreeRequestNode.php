<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\ContentTree;

use Ibexa\Rest\Value as RestValue;

final class LoadSubtreeRequestNode extends RestValue
{
    /**
     * @param \Ibexa\AdminUi\REST\Value\ContentTree\LoadSubtreeRequestNode[] $children
     */
    public function __construct(
        public int $locationId,
        public int $limit = 20,
        public int $offset = 0,
        public array $children = []
    ) {
    }
}
