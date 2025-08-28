<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ObjectState;

use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState;

final class ObjectStateDeleteData
{
    public function __construct(private ?ObjectState $objectState = null)
    {
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
