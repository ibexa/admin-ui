<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\Content;

use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

readonly class ContentDraft implements ContentDraftInterface
{
    public function __construct(
        private VersionInfo $versionInfo,
        private VersionId $versionId,
        private ContentType $contentType
    ) {
    }

    public function getVersionInfo(): VersionInfo
    {
        return $this->versionInfo;
    }

    public function getVersionId(): VersionId
    {
        return $this->versionId;
    }

    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    public function isAccessible(): bool
    {
        return true;
    }
}
