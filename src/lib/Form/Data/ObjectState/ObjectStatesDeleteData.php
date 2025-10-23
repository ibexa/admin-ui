<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ObjectState;

use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState;

final class ObjectStatesDeleteData
{
    /**
     * @param ObjectState[]|null $objectStates
     */
    public function __construct(private ?array $objectStates = [])
    {
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
    public function setObjectStates(?array $objectStates): void
    {
        $this->objectStates = $objectStates;
    }
}
