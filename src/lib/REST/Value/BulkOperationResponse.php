<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value;

use Ibexa\Rest\Value as RestValue;

final class BulkOperationResponse extends RestValue
{
    /**
     * @param \Ibexa\AdminUi\REST\Value\OperationResponse[] $operations
     */
    public function __construct(public readonly array $operations)
    {
    }
}
