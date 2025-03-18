<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\SubItems;

use Ibexa\Rest\Value;

final class SubItem extends Value
{
    public function __construct(
        public int $id,
        public string $remoteId,
        public bool $hidden,
        public int $priority,
        public string $pathString,
        public Thumbnail $contentThumbnail,
        public string $contentRemoteId,
        public string $mainLanguageCode,
        public Owner $owner,
        public int $currentVersionNo,
        public Owner $currentVersionOwner,
        public ContentType $contentType,
        public string $sectionName,
        public int $publishedDate,
        public int $modificationDate,
        public ?string $name = null,
    ) {
    }
}
