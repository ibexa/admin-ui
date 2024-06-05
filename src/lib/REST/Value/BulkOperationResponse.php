<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value;

use Ibexa\Rest\Value as RestValue;

class BulkOperationResponse extends RestValue
{
    /** @var \Ibexa\AdminUi\REST\Value\OperationResponse[] */
    public $operations;

    /**
     * @param \Ibexa\AdminUi\REST\Value\OperationResponse[] $operations
     */
    public function __construct($operations)
    {
        $this->operations = $operations;
    }
}
