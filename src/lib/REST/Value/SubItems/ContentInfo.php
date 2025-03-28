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
        public int $id,
        public string $remoteId,
        public string $mainLanguageCode,
        public string $sectionName,
        public int $publishedDate,
        public int $modificationDate,
        public ?string $name = null,
    ) {
    }
}
