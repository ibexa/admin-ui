<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Section;

use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo add validation
 */
class SectionUpdateData
{
    protected ?Section $section;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[[:alnum:]_]+$/', message: 'ez.section.identifier.format')]
    protected ?string $identifier;

    #[Assert\NotBlank]
    protected ?string $name;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Section|null $section
     */
    public function __construct(?Section $section = null)
    {
        $this->section = $section;

        if (null !== $section) {
            $this->identifier = $section->identifier;
            $this->name = $section->name;
        }
    }

    /**
     * @return string|null
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @param string|null $identifier
     */
    public function setIdentifier(?string $identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(?Section $section = null): void
    {
        $this->section = $section;
    }
}
