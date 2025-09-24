<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\Draft;

final class ContentRemoveData
{
    /**
     * @param \Ibexa\AdminUi\UI\Value\Content\VersionId[]|false[]|null $versions
     */
    public function __construct(private ?array $versions = null)
    {
    }

    /**
     * @return \Ibexa\AdminUi\UI\Value\Content\VersionId[]|false[]|null
     */
    public function getVersions(): ?array
    {
        return $this->versions;
    }

    /**
     * @param \Ibexa\AdminUi\UI\Value\Content\VersionId[]|false[]|null $versions
     */
    public function setVersions(?array $versions): self
    {
        $this->versions = $versions;

        return $this;
    }
}
