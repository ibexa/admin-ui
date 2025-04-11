<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo Add validation.
 */
class ContentMainLocationUpdateData
{
    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null
     */
    #[Assert\NotBlank]
    public $contentInfo;

    /**
     * @todo add more validation constraints
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Location
     */
    #[Assert\NotBlank]
    public $location;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null $contentInfo
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     */
    public function __construct(
        ContentInfo $contentInfo = null,
        Location $location = null
    ) {
        $this->contentInfo = $contentInfo;
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null $contentInfo
     */
    public function setContentInfo(?ContentInfo $contentInfo): void
    {
        $this->contentInfo = $contentInfo;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location|null
     */
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     */
    public function setLocation(Location $location): void
    {
        $this->location = $location;
    }
}
