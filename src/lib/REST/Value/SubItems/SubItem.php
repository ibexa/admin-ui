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
    /**
     * @param string[] $languagesCodes
     */
    public function __construct(
        public readonly int $id,
        public readonly string $remoteId,
        public readonly bool $hidden,
        public readonly bool $invisible,
        public readonly int $priority,
        public readonly string $pathString,
        public readonly Thumbnail $contentThumbnail,
        public readonly Owner $owner,
        public readonly int $currentVersionNo,
        public readonly array $languagesCodes,
        public readonly Owner $currentVersionOwner,
        public readonly ContentType $contentType,
        public readonly ContentInfo $contentInfo,
    ) {
    }
}
