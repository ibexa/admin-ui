<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\SubItems;

final class Thumbnail
{
    public function __construct(
        public ?string $uri,
        public ?string $mimeType
    ) {
    }
}
