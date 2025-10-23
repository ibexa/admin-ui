<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\SubItems;

use Ibexa\Rest\Value;

final class Owner extends Value
{
    public function __construct(
        public readonly int $id,
        public readonly Thumbnail $thumbnail,
        public readonly ContentType $contentType,
        public readonly ?string $name = null,
    ) {
    }
}
