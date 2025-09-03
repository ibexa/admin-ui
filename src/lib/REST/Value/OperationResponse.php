<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value;

use Ibexa\Rest\Value as RestValue;

final class OperationResponse extends RestValue
{
    /**
     * @param array<string, list<string|null>> $headers
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly array $headers,
        public readonly ?string $content = null
    ) {
    }
}
