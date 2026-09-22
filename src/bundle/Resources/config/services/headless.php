<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

use Ibexa\AdminUi\Headless\HeadlessConfiguration;
use Ibexa\AdminUi\Headless\HeadlessConfigurationInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->private();

    $services->set(HeadlessConfiguration::class);

    $services->alias(HeadlessConfigurationInterface::class, HeadlessConfiguration::class);
};
