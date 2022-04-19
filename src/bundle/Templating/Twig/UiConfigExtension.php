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
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Exports `ibexa_admin_ui_config` providing UI Config as a global Twig variable.
 */
class UiConfigExtension extends AbstractExtension implements GlobalsInterface
{
    /** @var \Twig\Environment */
    protected $twig;

    /** @var \Ibexa\AdminUi\UI\Config\Aggregator */
    protected $aggregator;

    /**
     * @param \Twig\Environment $twig
     * @param \Ibexa\AdminUi\UI\Config\Aggregator $aggregator
     */
    public function __construct(Environment $twig, Aggregator $aggregator)
    {
        $this->twig = $twig;
        $this->aggregator = $aggregator;
    }

    /**
     * @return array
     */
    public function getGlobals(): array
    {
        $configWrapper = $this->createConfigWrapper();

        return [
            /** @deprecated ez_admin_ui_config is deprecated since 4.0, use ibexa_admin_ui_config instead */
            'ez_admin_ui_config' => $configWrapper,
            'ibexa_admin_ui_config' => $configWrapper,
        ];
    }

    /**
     * Create lazy loaded configuration.
     *
     * @return \Ibexa\AdminUi\UI\Config\ConfigWrapper
     */
    private function createConfigWrapper(): ConfigWrapper
    {
        $factory = new LazyLoadingValueHolderFactory();
        $initializer = function (&$wrappedObject, LazyLoadingInterface $proxy, $method, array $parameters, &$initializer) {
            $initializer = null;
            $wrappedObject = new ConfigWrapper($this->aggregator->getConfig());

            return true;
        };

        return $factory->createProxy(ConfigWrapper::class, $initializer);
    }
}

class_alias(UiConfigExtension::class, 'EzSystems\EzPlatformAdminUiBundle\Templating\Twig\UiConfigExtension');
