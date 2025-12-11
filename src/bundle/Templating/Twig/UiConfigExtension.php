<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\AdminUi\UI\Config\Aggregator;
use Ibexa\AdminUi\UI\Config\ConfigWrapper;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Exports `ibexa_admin_ui_config` providing UI Config as a global Twig variable.
 */
final class UiConfigExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly Aggregator $aggregator
    ) {
    }

    /**
     * @return array<string, \Ibexa\AdminUi\UI\Config\ConfigWrapper>
     */
    public function getGlobals(): array
    {
        $configWrapper = $this->createConfigWrapper();

        return [
            'ibexa_admin_ui_config' => $configWrapper,
        ];
    }

    /**
     * Create lazy loaded configuration.
     */
    private function createConfigWrapper(): ConfigWrapper
    {
        $factory = new LazyLoadingValueHolderFactory();
        $initializer = function (&$wrappedObject, LazyLoadingInterface $proxy, $method, array $parameters, &$initializer): true {
            $initializer = null;
            $wrappedObject = new ConfigWrapper($this->aggregator->getConfig());

            return true;
        };

        return $factory->createProxy(ConfigWrapper::class, $initializer);
    }
}
