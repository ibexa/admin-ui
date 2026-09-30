<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\SubItems;

use Ibexa\Rest\Value;

final class ContentInfo extends Value
{
    public function __construct(
        public readonly int $id,
        public readonly string $remoteId,
        public readonly string $mainLanguageCode,
        public readonly int $publishedDate,
        public readonly int $modificationDate,
        public readonly ?string $sectionName,
        public readonly ?string $name = null,
    ) {
    }
}
