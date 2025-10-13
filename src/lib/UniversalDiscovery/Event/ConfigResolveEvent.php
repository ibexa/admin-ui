<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UniversalDiscovery\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ConfigResolveEvent extends Event
{
    public const string NAME = 'udw.resolve.config';

    protected string $configName;

    /** @var array<string, mixed> */
    protected array $config = [];

    /** @var array<mixed> */
    protected array $context = [];

    public function getConfigName(): string
    {
        return $this->configName;
    }

    public function setConfigName(string $configName): void
    {
        $this->configName = $configName;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @return array<mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array<mixed> $context
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }
}
