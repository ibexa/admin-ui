<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\AdminUi\Headless;

/**
 * Siteaccess-aware configuration of the headless mode, where the site is rendered by an external
 * front-end application (`ibexa.system.<scope>.headless`).
 *
 * All methods resolve the configuration for the current siteaccess unless `$scope` is given.
 *
 * `isEnabled()` only reflects the flag: consumers must also check that the URL they need is configured
 * (for example `getPageBuilderPreviewUrl() !== null`) before switching to the headless behaviour.
 */
interface HeadlessConfigurationInterface
{
    public function isEnabled(?string $scope = null): bool;

    /**
     * URL of the front-end application loaded into the Page Builder preview iframe.
     */
    public function getPageBuilderPreviewUrl(?string $scope = null): ?string;

    /**
     * URL of the front-end application used to preview content items outside of Page Builder.
     */
    public function getContentPreviewUrl(?string $scope = null): ?string;

    /**
     * URL of the page where the front-end application can be configured, if the installation provides one.
     */
    public function getConfigurationUrl(?string $scope = null): ?string;
}
