<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;

class ContentLocationAddData
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[] $newLocations
     */
    public function __construct(
        protected ?ContentInfo $contentInfo = null,
        protected array $newLocations = []
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
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
     */
    public function getNewLocations(): array
    {
        return $this->newLocations;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[] $newLocations
     */
    public function setNewLocations(array $newLocations): void
    {
        $this->newLocations = $newLocations;
    }
}
