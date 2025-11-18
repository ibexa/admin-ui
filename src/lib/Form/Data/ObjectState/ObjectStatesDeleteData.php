<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ObjectState;

use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState;

class ObjectStatesDeleteData
{
    /** @var ObjectState[]|null */
    protected $objectStates;

    /**
     * @param ObjectState[]|null $objectStates
     */
    public function __construct(array $objectStates = [])
    {
        $this->objectStates = $objectStates;
    }

    /**
     * @return ObjectState[]|null
     */
    public function getObjectStates(): ?array
    {
        return $this->objectStates;
    }

    /**
     * @param ObjectState[]|null $objectStates
     */
    public function setObjectStates(?array $objectStates)
    {
        $this->objectStates = $objectStates;
    }
}

class_alias(ObjectStatesDeleteData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\ObjectState\ObjectStatesDeleteData');
