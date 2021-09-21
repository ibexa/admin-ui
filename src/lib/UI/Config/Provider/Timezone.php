<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use EzSystems\EzPlatformUser\UserSetting\UserSettingService;
use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;

class Timezone implements ProviderInterface
{
    /** @var \EzSystems\EzPlatformUser\UserSetting\UserSettingService */
    protected $userSettingService;

    /**
     * @param \EzSystems\EzPlatformUser\UserSetting\UserSettingService $userSettingService
     */
    public function __construct(UserSettingService $userSettingService)
    {
        $this->userSettingService = $userSettingService;
    }

    /**
     * @inheritdoc
     *
     * @return string
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getConfig(): string
    {
        $timezone = $this->userSettingService->getUserSetting('timezone');

        return $timezone->value;
    }
}

class_alias(Timezone::class, 'EzSystems\EzPlatformAdminUi\UI\Config\Provider\Timezone');
