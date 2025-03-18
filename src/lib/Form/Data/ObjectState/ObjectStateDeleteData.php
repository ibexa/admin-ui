<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ObjectState;

use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState;

class ObjectStateDeleteData
{
    private ?ObjectState $objectState;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState|null $objectState
     */
    public function __construct(?ObjectState $objectState = null)
    {
        $this->objectState = $objectState;
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
    public function setObjectState(ObjectState $objectState): void
    {
        $this->objectState = $objectState;
    }
}
