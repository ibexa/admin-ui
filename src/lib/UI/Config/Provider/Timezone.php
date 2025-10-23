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

final readonly class Timezone implements ProviderInterface
{
    public function __construct(private UserSettingService $userSettingService) {}

    /**
     * @throws InvalidArgumentException
     * @throws UnauthorizedException
     */
    public function getConfig(): string
    {
        $timezone = $this->userSettingService->getUserSetting('timezone');

        return $timezone->value;
    }
}
