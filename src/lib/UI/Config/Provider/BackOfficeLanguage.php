<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Config\Provider;

use Ibexa\Contracts\AdminUi\UI\Config\ProviderInterface;
use Ibexa\User\UserSetting\UserSettingService;

final readonly class BackOfficeLanguage implements ProviderInterface
{
    public function __construct(private UserSettingService $userSettingService)
    {
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function getConfig(): string
    {
        return $this
            ->userSettingService
            ->getUserSetting('language')
            ->getValue();
    }
}
