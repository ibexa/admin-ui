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
    /**
     * @param array<int, mixed>|null $versions
     */
    public function __construct(
        protected ?ContentInfo $contentInfo = null,
        protected ?array $versions = []
    ) {
    }

    public function getContentInfo(): ?ContentInfo
    {
        return $this->contentInfo;
    }

    public function setContentInfo(?ContentInfo $contentInfo): void
    {
        $this->contentInfo = $contentInfo;
    }

    /**
     * @return array<int, mixed>|null
     */
    public function getVersions(): ?array
    {
        return $this->versions;
    }

    /**
     * @param array<int, mixed>|null $versions
     */
    public function setVersions(?array $versions): void
    {
        $this->versions = $versions;
    }
}
