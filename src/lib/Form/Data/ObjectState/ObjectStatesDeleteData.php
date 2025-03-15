<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ObjectState;

class ObjectStatesDeleteData
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState[]|null */
    protected array $objectStates;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState[]|null $objectStates
     */
    public function __construct(array $objectStates = [])
    {
        $this->objectStates = $objectStates;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState[]|null
     */
    public function getObjectStates(): ?array
    {
        return $this->objectStates;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState[]|null $objectStates
     */
    public function setObjectStates(?array $objectStates): void
    {
        $this->objectStates = $objectStates;
    }
}
