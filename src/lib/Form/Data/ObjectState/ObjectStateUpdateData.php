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
    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState|null
     */
    private $objectState;

    /** @var string */
    private $identifier;

    /** @var string */
    private $name;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState|null $objectState
     */
    public function __construct(?ObjectState $objectState = null)
    {
        if ($objectState instanceof ObjectState) {
            $this->objectState = $objectState;
            $this->name = $objectState->getName();
            $this->identifier = $objectState->identifier;
        }
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier(string $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState
     */
    public function getObjectState(): ObjectState
    {
        return $this->objectState;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState $objectState
     */
    public function setObjectState(ObjectState $objectState)
    {
        $this->objectState = $objectState;
    }
}

class_alias(ObjectStateUpdateData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\ObjectState\ObjectStateUpdateData');
