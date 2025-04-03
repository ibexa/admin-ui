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
        readonly public int $id,
        readonly public string $remoteId,
        readonly public bool $hidden,
        readonly public bool $invisible,
        readonly public int $priority,
        readonly public string $pathString,
        readonly public Thumbnail $contentThumbnail,
        readonly public Owner $owner,
        readonly public int $currentVersionNo,
        readonly public array $languagesCodes,
        readonly public Owner $currentVersionOwner,
        readonly public ContentType $contentType,
        readonly public ContentInfo $contentInfo,
    ) {
    }
}
