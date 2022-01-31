<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Service;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\Asset\Packages;

final class ContentTypeIconResolver
{
    private const DEFAULT_IDENTIFIER = 'default-config';
    private const PARAM_NAME_FORMAT = 'content_type.%s';

    private const ICON_KEY = 'thumbnail';

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    /** @var \Symfony\Component\Asset\Packages */
    private $packages;

    /**
     * @param \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface $configResolver
     * @param \Symfony\Component\Asset\Packages $packages
     */
    public function __construct(ConfigResolverInterface $configResolver, Packages $packages)
    {
        $this->configResolver = $configResolver;
        $this->packages = $packages;
    }

    /**
     * Returns path to content type icon.
     *
     * Path is resolved based on configuration (ezpublish.system.<SCOPE>.content_type.<IDENTIFIER>). If there isn't
     * corresponding entry for given content type, then path to default icon will be returned.
     *
     * @throws \Ibexa\AdminUi\Exception\ContentTypeIconNotFoundException
     */
    public function getContentTypeIcon(string $identifier): string
    {
        $icon = $this->resolveIcon($identifier);

        $fragment = null;
        if (strpos($icon, '#') !== false) {
            [$icon, $fragment] = explode('#', $icon);
        }

        return $this->packages->getUrl($icon) . ($fragment ? '#' . $fragment : '');
    }

    /**
     * @throws \Ibexa\AdminUi\Exception\ContentTypeIconNotFoundException
     */
    private function resolveIcon(string $identifier): string
    {
        $parameterName = $this->getConfigParameterName($identifier);
        $defaultParameterName = $this->getConfigParameterName(self::DEFAULT_IDENTIFIER);

        if ($this->configResolver->hasParameter($parameterName)) {
            $config = $this->configResolver->getParameter($parameterName);
        }

        if ((empty($config) || empty($config[self::ICON_KEY])) && $this->configResolver->hasParameter($defaultParameterName)) {
            $config = $this->configResolver->getParameter($defaultParameterName);
        }

        return $config[self::ICON_KEY] ?? '';
    }

    /**
     * Return configuration parameter name for given content type identifier.
     */
    private function getConfigParameterName(string $identifier): string
    {
        return sprintf(self::PARAM_NAME_FORMAT, $identifier);
    }
}

class_alias(ContentTypeIconResolver::class, 'EzSystems\EzPlatformAdminUi\UI\Service\ContentTypeIconResolver');
