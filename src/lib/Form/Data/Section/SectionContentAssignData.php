<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Section;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;

/**
 * @todo add validation
 */
class SectionContentAssignData
{
    /** @var Section|null */
    protected $section;

    /** @var Location[] */
    protected $locations;

    /**
     * @param Section|null $section
     * @param Location[] $locations
     */
    public function __construct(
        ?Section $section = null,
        array $locations = []
    ) {
        $this->section = $section;
        $this->locations = $locations;
    }

    /**
     * @return Section|null
     */
    public function getSection(): ?Section
    {
        return $this->section;
    }

    /**
     * @param Section|null $section
     */
    public function setSection(?Section $section)
    {
        $this->section = $section;
    }

    /**
     * @return Location[]
     */
    public function getLocations(): array
    {
        return $this->locations;
    }

    /**
     * @param Location[] $locations
     */
    public function setLocations(array $locations)
    {
        $this->locations = $locations;
    }
}

class_alias(SectionContentAssignData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\Section\SectionContentAssignData');
