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
final class SectionContentAssignData
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[] $locations
     */
    public function __construct(
        private ?Section $section = null,
        private array $locations = []
    ) {
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }

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
