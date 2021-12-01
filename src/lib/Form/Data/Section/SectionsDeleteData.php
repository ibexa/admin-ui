<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Section;

/**
 * @todo Add validation
 */
class SectionsDeleteData
{
    /** @var array|null */
    protected $sections;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Section[]|null $sections
     */
    public function __construct(array $sections = [])
    {
        $this->sections = $sections;
    }

    /**
     * @return array|null
     */
    public function getSections(): ?array
    {
        return $this->sections;
    }

    /**
     * @param array|null $sections
     */
    public function setSections(?array $sections)
    {
        $this->sections = $sections;
    }
}

class_alias(SectionsDeleteData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\Section\SectionsDeleteData');
