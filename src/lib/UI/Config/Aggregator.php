<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;

/**
 * Aggregates a set of ApplicationConfig Providers.
 */
class Aggregator
{
    /**
     * @param ProviderInterface[] $providers
     */
    public function __construct(private array $providers = []) {}

    public function addProvider(
        string $key,
        ProviderInterface $provider
    ): void {
        $this->providers[$key] = $provider;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function removeProvider(string $key): ProviderInterface
    {
        if (!isset($this->providers[$key])) {
            throw new InvalidArgumentException(
                'key',
                sprintf('Provider under key "%s" not found', $key)
            );
        }

        return $this->providers[$key];
    }

    /**
     * @return ProviderInterface[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * @param ProviderInterface[] $providers
     */
    public function setProviders(array $providers): void
    {
        $this->providers = $providers;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $config = [];
        foreach ($this->providers as $key => $provider) {
            $config[$key] = $provider->getConfig();
        }

        return $config;
    }
}
