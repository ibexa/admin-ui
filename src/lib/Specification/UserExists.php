<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\UserService;

final readonly class UserExists implements UserSpecification
{
    public function __construct(private UserService $userService)
    {
    }

    public function isSatisfiedBy(mixed $userId): bool
    {
        try {
            $this->userService->loadUser((int)$userId);

            return true;
        } catch (NotFoundException) {
            return false;
        }
    }
}
