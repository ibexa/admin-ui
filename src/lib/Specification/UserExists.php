<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Specification;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\UserService;

class UserExists implements UserSpecification
{
    /** @var \Ibexa\Contracts\Core\Repository\UserService */
    private $userService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Checks if $userId is an existing User id.
     *
     * @param mixed $userId
     *
     * @return bool
     */
    public function isSatisfiedBy($userId): bool
    {
        try {
            $this->userService->loadUser($userId);

            return true;
        } catch (NotFoundException $e) {
            return false;
        }
    }
}
