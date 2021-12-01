<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Version;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;

/**
 * @todo Add validation
 */
class VersionRemoveData
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null */
    protected $contentInfo;

    /** @var array|null */
    protected $versions;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null $contentInfo
     * @param array|null $versions
     */
    public function __construct(?ContentInfo $contentInfo = null, array $versions = [])
    {
        $this->contentInfo = $contentInfo;
        $this->versions = $versions;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null
     */
    public function getContentInfo(): ?ContentInfo
    {
        return $this->contentInfo;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null $contentInfo
     */
    public function setContentInfo(?ContentInfo $contentInfo)
    {
        $this->contentInfo = $contentInfo;
    }

    /**
     * @return array|null
     */
    public function getVersions(): ?array
    {
        return $this->versions;
    }

    /**
     * @param array|null $versions
     */
    public function setVersions(?array $versions)
    {
        $this->versions = $versions;
    }
}

class_alias(VersionRemoveData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\Version\VersionRemoveData');
