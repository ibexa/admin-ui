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
final class SectionUpdateData
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[[:alnum:]_]+$/', message: 'ez.section.identifier.format')]
    private ?string $identifier;

    #[Assert\NotBlank]
    private ?string $name;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Section|null $section
     */
    public function __construct(protected ?Section $section = null)
    {
        if (null !== $section) {
            $this->identifier = $section->identifier;
            $this->name = $section->name;
        }
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

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
