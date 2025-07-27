<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Module\Subitems\Values;

use Ibexa\Rest\Server\Values\RestContent;
use Ibexa\Rest\Server\Values\RestLocation;
use Ibexa\Rest\Value as RestValue;

class SubitemsRow extends RestValue
{
    /** @var \Ibexa\Rest\Server\Values\RestLocation */
    public $restLocation;

    /** @var \Ibexa\Rest\Server\Values\RestContent */
    public $restContent;

    /**
     * @param \Ibexa\Rest\Server\Values\RestLocation $restLocation
     * @param \Ibexa\Rest\Server\Values\RestContent $restContent
     */
    public function __construct(RestLocation $restLocation, RestContent $restContent)
    {
        $this->restLocation = $restLocation;
        $this->restContent = $restContent;
    }
}
