<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Templating\Twig;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\UserPreferenceService;
use Ibexa\Contracts\Core\Repository\Values\UserPreference\UserPreference;
use Twig\Extension\RuntimeExtensionInterface;

final class UserPreferenceRuntime implements RuntimeExtensionInterface
{
    private UserPreferenceService $userPreferenceService;

    public function __construct(
        UserPreferenceService $userPreferenceService
    ) {
        $this->userPreferenceService = $userPreferenceService;
    }

    public function getUserPreference(string $identifier): ?UserPreference
    {
        try {
            return $this->userPreferenceService->getUserPreference($identifier);
        } catch (NotFoundException|UnauthorizedException $e) {
            return null;
        }
    }
}
