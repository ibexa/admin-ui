<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\SubItems;

use Ibexa\Rest\Value;

final class Thumbnail extends Value
{
    public function __construct(
        readonly public ?string $uri,
        readonly public ?string $mimeType
    ) {
    }
}
