<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Resolver;

use Ibexa\Contracts\AdminUi\Resolver\IconPathResolverInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\Asset\Packages;

/**
 * @internal
 */
final class IconPathResolver implements IconPathResolverInterface
{
    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    /** @var \Symfony\Component\Asset\Packages */
    private $packages;

    public function __construct(
        ConfigResolverInterface $configResolver,
        Packages $packages
    ) {
        $this->configResolver = $configResolver;
        $this->packages = $packages;
    }

    public function resolve(string $icon, ?string $set = null): string
    {
        $iconSetName = $set ?? $this->configResolver->getParameter('assets.default_icon_set');
        $iconSets = $this->configResolver->getParameter('assets.icon_sets');

        return sprintf('%s#%s', $this->packages->getUrl($iconSets[$iconSetName]), $icon);
    }
}

class_alias(IconPathResolver::class, 'Ibexa\Platform\Assets\Resolver\IconPathResolver');
