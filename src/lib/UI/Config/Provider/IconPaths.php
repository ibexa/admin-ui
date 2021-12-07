<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Core\MVC\ConfigResolverInterface;

/**
 * @internal
 */
final class IconPaths implements ProviderInterface
{
    /** @var \Ibexa\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function getConfig(): array
    {
        return [
            'iconSets' => $this->configResolver->getParameter('assets.icon_sets'),
            'defaultIconSet' => $this->configResolver->getParameter('assets.default_icon_set'),
        ];
    }
}

class_alias(IconPaths::class, 'EzSystems\EzPlatformAdminUi\UI\Config\Provider\IconPaths');
