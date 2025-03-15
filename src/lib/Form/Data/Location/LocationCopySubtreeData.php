<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Data\Location;

use Ibexa\AdminUi\Validator\Constraints as AdminUiAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

class LocationCopySubtreeData extends AbstractLocationCopyData
{
    /**
     * @AdminUiAssert\LocationIsWithinCopySubtreeLimit()
     *
     * @AdminUiAssert\LocationIsNotRoot()
     */
    #[Assert\NotNull]
    protected ?Location $location;

    /**
     * @AdminUiAssert\LocationIsContainer()
     *
     * @AdminUiAssert\LocationIsNotSubLocation(
     *     propertyPath="location"
     * )
     */
    #[Assert\NotNull]
    protected ?Location $newParentLocation;
}
