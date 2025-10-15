<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider\Module;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\User\UserSetting\UserSettingService;

/**
 * Provides information about current setting for sub-items list.
 */
final readonly class SubItemsList implements ProviderInterface
{
    public function __construct(private UserSettingService $userSettingService)
    {
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function getConfig(): array
    {
        return [
            'limit' => (int)$this
                ->userSettingService
                ->getUserSetting('subitems_limit')
                ->getValue(),
        ];
    }
}
