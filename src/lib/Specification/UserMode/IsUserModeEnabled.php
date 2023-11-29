<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\UserMode;

use Ibexa\AdminUi\UserSetting\UserMode;
use Ibexa\Contracts\Core\Specification\AbstractSpecification;
use Ibexa\User\UserSetting\UserSettingService;

final class IsUserModeEnabled extends AbstractSpecification
{
    private string $mode;

    public function __construct(string $mode)
    {
        $this->mode = $mode;
    }

    /**
     * @param string $item
     */
    public function isSatisfiedBy($item): bool
    {
        return $this->mode === $item;
    }

    public static function fromUserSettings(UserSettingService $userService): self
    {
        return new self($userService->getUserSetting(UserMode::IDENTIFIER)->value);
    }
}
