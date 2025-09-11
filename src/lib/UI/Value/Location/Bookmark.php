<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\Content\Location as CoreLocation;

class Bookmark extends CoreLocation
{
    protected ContentType $contentType;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location[] */
    protected array $pathLocations;

    protected bool $userCanEdit;

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct(
        readonly Location $location,
        readonly array $properties = []
    ) {
        parent::__construct(get_object_vars($location) + $properties);
    }
}
