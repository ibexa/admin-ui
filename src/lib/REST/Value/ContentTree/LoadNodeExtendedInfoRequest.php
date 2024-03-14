<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\ContentTree;

use Ibexa\Rest\Value as RestValue;

class LoadNodeExtendedInfoRequest extends RestValue
{
    public int $locationId;

    public function __construct(int $locationId)
    {
        $this->locationId = $locationId;
    }
}
