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
    /**
     * @var ObjectState|null
     */
    private $objectState;

    /**
     * @param ObjectState|null $objectState
     */
    public function __construct(?ObjectState $objectState = null)
    {
        $this->objectState = $objectState;
    }

    /**
     * @return ObjectState
     */
    public function getObjectState(): ObjectState
    {
        return $this->objectState;
    }

    /**
     * @param ObjectState $objectState
     */
    public function setObjectState(ObjectState $objectState)
    {
        $this->objectState = $objectState;
    }
}

class_alias(ObjectStateDeleteData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\ObjectState\ObjectStateDeleteData');
