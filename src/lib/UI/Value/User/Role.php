<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value\User;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation\RoleLimitation as APIRoleLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Role as APIRole;
use Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment;

class Role extends RoleAssignment
{
    protected ?APIRoleLimitation $limitation;

    protected APIRole $role;

    public function getRoleLimitation(): ?APIRoleLimitation
    {
        return $this->limitation;
    }

    public function getRole(): APIRole
    {
        return $this->role;
    }

    /**
     * @param array<string, mixed> $properties
     */
    public function __construct(
        readonly RoleAssignment $roleAssignment,
        readonly array $properties = []
    ) {
        parent::__construct(get_object_vars($roleAssignment) + $properties);

        $this->role = $roleAssignment->getRole();
        $this->limitation = $roleAssignment->getRoleLimitation();
    }
}
