<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Siteaccess;

use Ibexa\Contracts\AdminUi\Siteaccess\SiteAccessInfo;
use Ibexa\Contracts\AdminUi\Siteaccess\SiteAccessInfoResolverInterface;
use Ibexa\Core\MVC\Symfony\SiteAccess;

final class SiteAccessInfoResolver implements SiteAccessInfoResolverInterface
{
    /**
     * @param iterable<\Ibexa\Contracts\AdminUi\Siteaccess\SiteAccessInfoProviderInterface> $siteAccessInfoProviders
     */
    public function __construct(
        private readonly iterable $siteAccessInfoProviders
    ) {
    }

    public function resolve(SiteAccess $siteAccess): SiteAccessInfo
    {
        foreach ($this->siteAccessInfoProviders as $siteAccessInfoProvider) {
            if ($siteAccessInfoProvider->supports($siteAccess)) {
                return $siteAccessInfoProvider->resolve($siteAccess);
            }
        }

        return new SiteAccessInfo($siteAccess->name);
    }
}
