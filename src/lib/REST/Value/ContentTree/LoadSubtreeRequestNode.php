<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\ContentTree;

use Ibexa\Rest\Value as RestValue;

class LoadSubtreeRequestNode extends RestValue
{
    /** @var int */
    public $locationId;

    /** @var int */
    public $limit;

    /** @var int */
    public $offset;

    /** @var \Ibexa\AdminUi\REST\Value\ContentTree\LoadSubtreeRequestNode[] */
    public $children;

    /**
     * @param int $locationId
     * @param int $limit
     * @param int $offset
     * @param \Ibexa\AdminUi\REST\Value\ContentTree\LoadSubtreeRequestNode[] $children
     */
    public function __construct(
        int $locationId,
        int $limit = 20,
        int $offset = 0,
        array $children = []
    ) {
        $this->locationId = $locationId;
        $this->children = $children;
        $this->limit = $limit;
        $this->offset = $offset;
    }
}
