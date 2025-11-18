<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider\Module;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\User\UserSetting\UserSettingService;

/**
 * Provides information about current setting for sub-items list.
 */
class SubItemsList implements ProviderInterface
{
    /** @var UserSettingService */
    private $userSettingService;

    /**
     * @param UserSettingService $userSettingService
     */
    public function __construct(UserSettingService $userSettingService)
    {
        $this->userSettingService = $userSettingService;
    }

    /**
     * @return array
     *
     * @throws InvalidArgumentException
     * @throws UnauthorizedException
     */
    public function getConfig(): array
    {
        return [
            'limit' => (int)$this->userSettingService->getUserSetting('subitems_limit')->value,
        ];
    }
}

class_alias(SubItemsList::class, 'EzSystems\EzPlatformAdminUi\UI\Config\Provider\Module\SubItemsList');
