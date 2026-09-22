<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Headless;

use Ibexa\Contracts\AdminUi\Headless\HeadlessConfigurationInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

final readonly class HeadlessConfiguration implements HeadlessConfigurationInterface
{
    public function __construct(private ConfigResolverInterface $configResolver)
    {
    }

    public function isEnabled(?string $scope = null): bool
    {
        return (bool)$this->configResolver->getParameter('headless.enabled', null, $scope);
    }

    public function getPageBuilderPreviewUrl(?string $scope = null): ?string
    {
        return $this->getUrl('headless.page_builder.preview_url', $scope);
    }

    public function getContentPreviewUrl(?string $scope = null): ?string
    {
        return $this->getUrl('headless.content.preview_url', $scope);
    }

    public function getConfigurationUrl(?string $scope = null): ?string
    {
        return $this->getUrl('headless.configuration_url', $scope);
    }

    private function getUrl(string $parameterName, ?string $scope): ?string
    {
        $value = $this->configResolver->getParameter($parameterName, null, $scope);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
