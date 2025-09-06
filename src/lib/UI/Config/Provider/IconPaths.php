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
final readonly class IconPaths implements ProviderInterface
{
    public function __construct(private ConfigResolverInterface $configResolver)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return [
            'iconSets' => $this->configResolver->getParameter('assets.icon_sets'),
            'defaultIconSet' => $this->configResolver->getParameter('assets.default_icon_set'),
            'iconAliases' => $this->configResolver->getParameter('assets.icon_aliases'),
        ];
    }
}
