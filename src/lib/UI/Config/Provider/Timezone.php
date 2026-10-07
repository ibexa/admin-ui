<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\User\UserSetting\UserSettingService;

class Timezone implements ProviderInterface
{
    /** @var UserSettingService */
    protected $userSettingService;

    /**
     * @param UserSettingService $userSettingService
     */
    public function __construct(UserSettingService $userSettingService)
    {
        $this->userSettingService = $userSettingService;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     *
     * @throws InvalidArgumentException
     * @throws UnauthorizedException
     */
    public function getConfig(): string
    {
        $timezone = $this->userSettingService->getUserSetting('timezone');

        return $timezone->value;
    }
}

class_alias(Timezone::class, 'EzSystems\EzPlatformAdminUi\UI\Config\Provider\Timezone');
