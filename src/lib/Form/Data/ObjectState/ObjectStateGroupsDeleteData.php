<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\ObjectState;

use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;

final class ObjectStateGroupsDeleteData
{
    /**
     * @param ObjectStateGroup[]|null $objectStateGroups
     */
    public function __construct(private ?array $objectStateGroups = [])
    {
    }

    /**
     * @return ObjectStateGroup[]|null
     */
    public function getObjectStateGroups(): ?array
    {
        return $this->objectStateGroups;
    }

    /**
     * @param ObjectStateGroup[]|null $objectStateGroups
     */
    public function setObjectStateGroups(?array $objectStateGroups): void
    {
        $this->objectStateGroups = $objectStateGroups;
    }
}
