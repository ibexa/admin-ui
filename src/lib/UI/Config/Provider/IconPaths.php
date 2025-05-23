<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

/**
 * @internal
 */
final class IconPaths implements ProviderInterface
{
    private ConfigResolverInterface $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function getConfig(): array
    {
        return [
            'iconSets' => $this->configResolver->getParameter('assets.icon_sets'),
            'defaultIconSet' => $this->configResolver->getParameter('assets.default_icon_set'),
            'iconMap' => $this->configResolver->getParameter('assets.icon_map'),
        ];
    }
}
