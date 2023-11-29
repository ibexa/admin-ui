<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\AdminUi\UserProfile\UserProfileConfigurationInterface;
use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;

final class UserProfile implements ProviderInterface
{
    private UserProfileConfigurationInterface $configuration;

    public function __construct(UserProfileConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return [
            'enabled' => $this->configuration->isEnabled(),
            'content_types' => $this->configuration->getContentTypes(),
        ];
    }
}
