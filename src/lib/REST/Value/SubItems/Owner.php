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
        readonly public int $id,
        readonly public Thumbnail $thumbnail,
        readonly public ContentType $contentType,
        readonly public ?string $name = null,
    ) {
    }
}
