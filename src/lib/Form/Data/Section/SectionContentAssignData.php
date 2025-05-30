<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Section;

use Ibexa\Contracts\Core\Repository\Values\Content\Section;

/**
 * @todo add validation
 */
class SectionContentAssignData
{
    protected ?Section $section;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location[] */
    protected array $locations;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Section|null $section
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[] $locations
     */
    public function __construct(?Section $section = null, array $locations = [])
    {
        $this->section = $section;
        $this->locations = $locations;
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
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
     */
    public function getLocations(): array
    {
        return $this->locations;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[] $locations
     */
    public function setLocations(array $locations): void
    {
        $this->locations = $locations;
    }
}
