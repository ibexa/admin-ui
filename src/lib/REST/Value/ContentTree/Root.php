<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\ContentTree;

use Ibexa\Rest\Value as RestValue;

final class Root extends RestValue
{
    /**
     * @param \Ibexa\AdminUi\REST\Value\ContentTree\Node[] $elements
     */
    public function __construct(public array $elements = [])
    {
    }
}
