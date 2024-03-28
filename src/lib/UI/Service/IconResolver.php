<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Service;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\Asset\Packages;

abstract class IconResolver
{
    protected const DEFAULT_IDENTIFIER = 'default-config';
    protected const ICON_KEY = 'thumbnail';

    private ConfigResolverInterface $configResolver;

    protected Packages $packages;

    public function __construct(ConfigResolverInterface $configResolver, Packages $packages)
    {
        $this->configResolver = $configResolver;
        $this->packages = $packages;
    }

    protected function getIcon(string $format, string $identifier): string
    {
        $icon = $this->resolveIcon($format, $identifier);
        $fragment = null;
        if (strpos($icon, '#') !== false) {
            [$icon, $fragment] = explode('#', $icon);
        }

        return $this->packages->getUrl($icon) . ($fragment ? '#' . $fragment : '');
    }

    private function resolveIcon(string $format, string $identifier): string
    {
        $parameterName = $this->getConfigParameterName($format, $identifier);
        $defaultParameterName = $this->getConfigParameterName($format, static::DEFAULT_IDENTIFIER);

        if ($this->configResolver->hasParameter($parameterName)) {
            $config = $this->configResolver->getParameter($parameterName);
        }

        if ((empty($config) || empty($config[static::ICON_KEY])) && $this->configResolver->hasParameter($defaultParameterName)) {
            $config = $this->configResolver->getParameter($defaultParameterName);
        }

        return $config[static::ICON_KEY] ?? '';
    }

    /**
     * Return configuration parameter name for given content type identifier.
     */
    private function getConfigParameterName(string $format, string $identifier): string
    {
        return sprintf($format, $identifier);
    }
}
