<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UniversalDiscovery;

use Ibexa\AdminUi\UniversalDiscovery\Event\ConfigResolveEvent;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConfigResolver
{
    private const UDW_CONFIG_PARAM_NAME = 'universal_discovery_widget_module.configuration';

    public const DEFAULT_CONFIGURATION_KEY = '_default';

    protected EventDispatcherInterface $eventDispatcher;

    protected ConfigResolverInterface $configResolver;

    /**
     * @param \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface $configResolver
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ConfigResolverInterface $configResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->configResolver = $configResolver;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $configName
     * @param array $context
     *
     * @return array
     */
    public function getConfig(string $configName, array $context = []): array
    {
        $config = $this->getUDWConfiguration($configName);
        $defaults = $this->getUDWConfiguration(self::DEFAULT_CONFIGURATION_KEY);

        $config = $this->mergeConfiguration($defaults, $config);

        $configResolveEvent = new ConfigResolveEvent();

        $configResolveEvent->setConfigName($configName);
        $configResolveEvent->setContext($context);
        $configResolveEvent->setConfig($config);

        /** @var \Ibexa\AdminUi\UniversalDiscovery\Event\ConfigResolveEvent $event */
        $event = $this->eventDispatcher->dispatch($configResolveEvent, ConfigResolveEvent::NAME);

        return $event->getConfig();
    }

    /**
     * @param array $default
     * @param mixed $apply
     *
     * @return array
     */
    protected function mergeConfiguration(array $default, $apply): array
    {
        foreach ($apply as $key => $item) {
            if (isset($default[$key]) && $this->isAssocArray($default[$key])) {
                $default[$key] = $this->mergeConfiguration($default[$key], $item);
            } else {
                $default[$key] = $item;
            }
        }

        return $default;
    }

    /**
     * Checks if item is associative array type.
     *
     * @param mixed $item
     *
     * @return bool
     */
    private function isAssocArray($item): bool
    {
        if (!is_array($item)) {
            // Is not an array at all
            return false;
        }

        if ($item === []) {
            // Treat empty array as Sequential
            return false;
        }

        // Check if keys are equal to sequence of 0 .. n-1
        return array_keys($item) !== range(0, count($item) - 1);
    }

    /**
     * Get UDW configuration from ConfigResolver.
     *
     * It's intentionally not cached as a scope changes dynamically.
     *
     * @param string $configName
     *
     * @return array
     */
    private function getUDWConfiguration(string $configName): array
    {
        $udwConfiguration = $this->configResolver->hasParameter(self::UDW_CONFIG_PARAM_NAME)
            ? $this->configResolver->getParameter(self::UDW_CONFIG_PARAM_NAME)
            : [];

        return $udwConfiguration[$configName] ?? [];
    }
}
