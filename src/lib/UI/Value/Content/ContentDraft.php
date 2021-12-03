<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\Content;

use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

class ContentDraft implements ContentDraftInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo */
    private $versionInfo;

    /** @var \Ibexa\AdminUi\UI\Value\Content\VersionId */
    private $versionId;

    /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType */
    private $contentType;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $versionInfo
     * @param \Ibexa\AdminUi\UI\Value\Content\VersionId $versionId
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     */
    public function __construct(
        VersionInfo $versionInfo,
        VersionId $versionId,
        ContentType $contentType
    ) {
        $this->versionInfo = $versionInfo;
        $this->versionId = $versionId;
        $this->contentType = $contentType;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo
     */
    public function getVersionInfo(): VersionInfo
    {
        return $this->versionInfo;
    }

    /**
     * @return \Ibexa\AdminUi\UI\Value\Content\VersionId
     */
    public function getVersionId(): VersionId
    {
        return $this->versionId;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType
     */
    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    /**
     * @return bool
     */
    public function isAccessible(): bool
    {
        return true;
    }
}

class_alias(ContentDraft::class, 'EzSystems\EzPlatformAdminUi\UI\Value\Content\ContentDraft');
