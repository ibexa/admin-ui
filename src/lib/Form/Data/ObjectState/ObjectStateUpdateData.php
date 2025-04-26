<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ObjectState;

use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState;

class ObjectStateUpdateData
{
    private ?ObjectState $objectState;

    private ?string $identifier = null;

    private ?string $name = null;

    public function __construct(?ObjectState $objectState = null)
    {
        $this->objectState = $objectState;
        if ($objectState instanceof ObjectState) {
            $this->name = $objectState->getName();
            $this->identifier = $objectState->identifier;
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

    public function getObjectState(): ?ObjectState
    {
        return $this->objectState;
    }

    public function setObjectState(?ObjectState $objectState): void
    {
        $this->objectState = $objectState;
    }
}
