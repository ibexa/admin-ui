<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Role;

final class RolesDeleteData
{
    /**
     * @param array<int, mixed>|null $roles
     */
    public function __construct(private ?array $roles = [])
    {
    }

    /**
     * @return array<int, mixed>|null
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }

    /**
     * @param array<int, mixed>|null $roles
     */
    public function setRoles(?array $roles): void
    {
        $this->roles = $roles;
    }
}
