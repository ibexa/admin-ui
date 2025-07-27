<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Symfony\Component\Validator\Constraints as Assert;

class LocationAssignSubtreeData
{
    #[Assert\NotBlank]
    protected ?Section $section;

    #[Assert\NotBlank]
    protected ?Location $location;

    public function __construct(?Section $section = null, ?Location $location = null)
    {
        $this->section = $section;
        $this->location = $location;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Section|null
     */
    public function getSection(): ?Section
    {
        return $this->section;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Section|null $section
     */
    public function setSection(?Section $section): void
    {
        $this->section = $section;
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
