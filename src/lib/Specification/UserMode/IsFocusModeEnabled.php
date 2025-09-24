<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification\UserMode;

use Ibexa\AdminUi\UserSetting\FocusMode;
use Ibexa\Contracts\Core\Specification\AbstractSpecification;
use Ibexa\User\UserSetting\UserSettingService;

final class IsFocusModeEnabled extends AbstractSpecification
{
    public function __construct(private readonly string $enabled)
    {
    }

    /**
     * @param string $item
     */
    public function isSatisfiedBy(mixed $item): bool
    {
        return $this->enabled === $item;
    }

    public static function fromUserSettings(UserSettingService $userService): self
    {
        return new self(
            $userService->getUserSetting(FocusMode::IDENTIFIER)->getValue()
        );
    }
}
