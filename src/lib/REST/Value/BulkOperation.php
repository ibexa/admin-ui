<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value;

use Ibexa\Rest\Value as RestValue;

class BulkOperation extends RestValue
{
    /** @var Operation[] */
    public $operations;

    /**
     * @param Operation[] $operations
     */
    public function __construct(array $operations)
    {
        $this->operations = $operations;
    }
}

class_alias(BulkOperation::class, 'EzSystems\EzPlatformAdminUi\REST\Value\BulkOperation');
