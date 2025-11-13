<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller\SiteAccess;

use Ibexa\AdminUi\REST\Value\SiteAccess\SiteAccessesList;
use Ibexa\AdminUi\Siteaccess\SiteaccessResolverInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Rest\Server\Controller as RestController;

final class SiteAccessController extends RestController
{
    private SiteaccessResolverInterface $nonAdminSiteAccessResolver;

    public function __construct(SiteaccessResolverInterface $nonAdminSiteAccessResolver)
    {
        $this->nonAdminSiteAccessResolver = $nonAdminSiteAccessResolver;
    }

    public function loadNonAdminSiteAccessesForLocation(Location $location): SiteAccessesList
    {
        return new SiteAccessesList(
            $this->nonAdminSiteAccessResolver->getSiteAccessesListForLocation($location)
        );
    }
}
