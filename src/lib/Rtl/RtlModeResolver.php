<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Rtl;

use Ibexa\Contracts\AdminUi\Rtl\RtlModeResolverInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\User\UserSetting\UserSettingService;

final readonly class RtlModeResolver implements RtlModeResolverInterface
{
    /**
     * @param list<string> $rtlLanguages
     */
    public function __construct(
        private UserSettingService $userSettingService,
        private array $rtlLanguages = [],
    ) {
    }

    public function isRtl(): bool
    {
        try {
            $userLanguage = $this->userSettingService->getUserSetting('language')->getValue();
        } catch (InvalidArgumentException | UnauthorizedException) {
            return false;
        }

        return in_array($userLanguage, $this->rtlLanguages, true);
    }
}
