<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Module\Subitems\Values;

use Ibexa\Rest\Server\Values\RestContent;
use Ibexa\Rest\Server\Values\RestLocation;
use Ibexa\Rest\Value as RestValue;

final class SubitemsRow extends RestValue
{
    public function __construct(
        public readonly RestLocation $restLocation,
        public readonly RestContent $restContent
    ) {
    }
}
