<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

class ContentVisibilityUpdateData
{
    private ?ContentInfo $contentInfo;

    private ?bool $visible;

    private ?Location $location;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null $contentInfo
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     * @param bool|null $visible
     */
    public function __construct(
        ContentInfo $contentInfo = null,
        Location $location = null,
        bool $visible = null
    ) {
        $this->contentInfo = $contentInfo;
        $this->visible = $visible;
        $this->location = $location;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null
     */
    public function getContentInfo(): ?ContentInfo
    {
        return $this->contentInfo;
    }

    /**
     * @return bool|null
     */
    public function getVisible(): ?bool
    {
        return $this->visible;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo
     */
    public function setContentInfo(ContentInfo $contentInfo): void
    {
        $this->contentInfo = $contentInfo;
    }

    /**
     * @param bool $visible
     */
    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location|null
     */
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     */
    public function setLocation(?Location $location): void
    {
        $this->location = $location;
    }
}
