<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\Draft;

use Ibexa\AdminUi\UI\Value\Content\VersionId;

final class ContentRemoveData
{
    /**
     * @param VersionId[]|false[]|null $versions
     */
    public function __construct(private ?array $versions = null) {}

    /**
     * @return VersionId[]|false[]|null
     */
    public function getVersions(): ?array
    {
        return $this->versions;
    }

    /**
     * @param VersionId[]|false[]|null $versions
     */
    public function setVersions(?array $versions): self
    {
        $this->versions = $versions;

        return $this;
    }
}
