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
final class SectionsDeleteData
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Section[]|null $sections
     */
    public function __construct(private ?array $sections = [])
    {
        $this->sections = $sections;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Section[]|null
     */
    public function getSections(): ?array
    {
        return $this->sections;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Section[]|null $sections
     */
    public function setSections(?array $sections): void
    {
        $this->sections = $sections;
    }
}
