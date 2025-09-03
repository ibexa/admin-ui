<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value;

use Ibexa\Rest\Value as RestValue;

final class Operation extends RestValue
{
    /**
     * @param string[] $headers
     */
    public function __construct(
        public readonly string $uri,
        public readonly string $method,
        public readonly array $parameters,
        public readonly array $headers,
        public readonly string $content
    ) {
    }
}
