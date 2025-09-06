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
    private const string UDW_CONFIG_PARAM_NAME = 'universal_discovery_widget_module.configuration';

    public const string DEFAULT_CONFIGURATION_KEY = '_default';

    public function __construct(
        protected readonly ConfigResolverInterface $configResolver,
        protected readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @param array<mixed> $context
     *
     * @return array<mixed>
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
     * @param array<string, mixed> $default
     *
     * @return array<string, mixed>
     */
    protected function mergeConfiguration(array $default, mixed $apply): array
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

    private function isAssocArray(mixed $item): bool
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
     * @return array<string, mixed>
     */
    private function getUDWConfiguration(string $configName): array
    {
        $udwConfiguration = $this->configResolver->hasParameter(self::UDW_CONFIG_PARAM_NAME)
            ? $this->configResolver->getParameter(self::UDW_CONFIG_PARAM_NAME)
            : [];

        return $udwConfiguration[$configName] ?? [];
    }
}
