<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider\Module;

use Ibexa\AdminUi\UniversalDiscovery\ConfigResolver;
use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;

/**
 * Provides information about the id of starting Location for the Universal Discovery Widget.
 */
final readonly class UniversalDiscoveryWidget implements ProviderInterface
{
    public function __construct(
        private ConfigResolver $configResolver
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        /* config structure has to reflect UDW module's config structure */
        return [
            'startingLocationId' => $this->getStartingLocationId(),
        ];
    }

    private function getStartingLocationId(): ?int
    {
        return $this->configResolver->getConfig(
            ConfigResolver::DEFAULT_CONFIGURATION_KEY
        )['starting_location_id'];
    }
}
