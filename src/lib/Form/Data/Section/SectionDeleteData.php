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
class SectionDeleteData
{
    /** @var Section|null */
    protected $section;

    /**
     * @param Section|null $section
     */
    public function __construct(?Section $section = null)
    {
        $this->section = $section;
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
}

class_alias(SectionDeleteData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\Section\SectionDeleteData');
