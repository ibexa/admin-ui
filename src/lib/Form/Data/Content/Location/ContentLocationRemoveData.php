<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Content\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Symfony\Component\Validator\Constraints as Assert;

class ContentLocationRemoveData
{
    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null
     */
    #[Assert\NotBlank]
    public $contentInfo;

    /**
     * @todo add more validation constraints
     *
     * @var array
     */
    #[Assert\NotBlank]
    public $locations;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null $contentInfo
     * @param array $selectedLocations
     */
    public function __construct(
        ?ContentInfo $contentInfo = null,
        array $selectedLocations = []
    ) {
        $this->contentInfo = $contentInfo;
        $this->locations = $selectedLocations;
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
     * @return array
     */
    public function getLocations(): array
    {
        return $this->locations;
    }

    /**
     * @param array $locations
     */
    public function setLocations(array $locations)
    {
        $this->locations = $locations;
    }
}
