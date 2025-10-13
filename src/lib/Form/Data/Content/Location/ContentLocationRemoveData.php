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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[] $selectedLocations
     */
    public function __construct(
        #[Assert\NotBlank]
        public ?ContentInfo $contentInfo = null,
        #[Assert\NotBlank]
        public array $selectedLocations = []
    ) {
    }

    public function setContentInfo(?ContentInfo $contentInfo): void
    {
        $this->contentInfo = $contentInfo;
    }

    public function getContentInfo(): ?ContentInfo
    {
        return $this->contentInfo;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[] $locations
     */
    public function setLocations(array $locations): void
    {
        $this->selectedLocations = $locations;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
     */
    public function getLocations(): array
    {
        return $this->selectedLocations;
    }
}
