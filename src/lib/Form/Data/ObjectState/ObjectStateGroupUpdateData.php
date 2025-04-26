<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ObjectState;

use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;

class ObjectStateGroupUpdateData
{
    private ?ObjectStateGroup $objectStateGroup;

    private ?string $identifier = null;

    private ?string $name = null;

    public function __construct(?ObjectStateGroup $objectStateGroup = null)
    {
        $this->objectStateGroup = $objectStateGroup;
        if ($objectStateGroup instanceof ObjectStateGroup) {
            $this->name = $objectStateGroup->getName();
            $this->identifier = $objectStateGroup->identifier;
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

    public function getObjectStateGroup(): ?ObjectStateGroup
    {
        return $this->objectStateGroup;
    }

    public function setObjectStateGroup(?ObjectStateGroup $objectStateGroup): void
    {
        $this->objectStateGroup = $objectStateGroup;
    }
}
