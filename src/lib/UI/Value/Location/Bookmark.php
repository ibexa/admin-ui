<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\UI\Value\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\Content\Location as CoreLocation;

class Bookmark extends CoreLocation
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType */
    protected $contentType;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location[] */
    protected $pathLocations;

    /** @var bool */
    protected $userCanEdit;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     * @param array $properties
     */
    public function __construct(Location $location, array $properties = [])
    {
        parent::__construct(get_object_vars($location) + $properties);
    }
}
