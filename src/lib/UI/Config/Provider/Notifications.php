<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

/**
 * Provides information about notifications.
 */
final readonly class Notifications implements ProviderInterface
{
    public const array NOTIFICATION_TYPES = ['error', 'warning', 'info', 'success'];

    public function __construct(private ConfigResolverInterface $configResolver)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $config = [];
        foreach (self::NOTIFICATION_TYPES as $type) {
            $config[$type] = [
                'timeout' => $this->configResolver->getParameter(
                    sprintf('notifications.%s.timeout', $type)
                ),
            ];
        }

        return $config;
    }
}
