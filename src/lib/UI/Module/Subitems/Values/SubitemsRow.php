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
    /** @var RestLocation */
    public $restLocation;

    /** @var RestContent */
    public $restContent;

    /**
     * @param RestLocation $restLocation
     * @param RestContent $restContent
     */
    public function __construct(
        RestLocation $restLocation,
        RestContent $restContent
    ) {
        $this->restLocation = $restLocation;
        $this->restContent = $restContent;
    }
}

class_alias(SubitemsRow::class, 'EzSystems\EzPlatformAdminUi\UI\Module\Subitems\Values\SubitemsRow');
