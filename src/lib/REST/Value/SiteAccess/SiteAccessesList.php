<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\REST\Value\SiteAccess;

use Ibexa\Core\MVC\Symfony\SiteAccess;
use Ibexa\Rest\Value as RestValue;

final class SiteAccessesList extends RestValue
{
    /** @var SiteAccess[] */
    private array $siteAccesses;

    /**
     * @param SiteAccess[] $siteAccesses
     */
    public function __construct(array $siteAccesses = [])
    {
        $this->siteAccesses = $siteAccesses;
    }

    /**
     * @return SiteAccess[]
     */
    public function getSiteAccesses(): array
    {
        return $this->siteAccesses;
    }
}
