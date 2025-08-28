<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Policy;

use Ibexa\Contracts\Core\Repository\Values\User\Role;

/**
 * @todo Add validation
 */
final class PoliciesDeleteData
{
    /**
     * @param array<int, mixed>|null $policies
     */
    public function __construct(
        private ?Role $role = null,
        private ?array $policies = []
    ) {
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): void
    {
        $this->role = $role;
    }

    /**
     * @return array<int, mixed>|null
     */
    public function getPolicies(): ?array
    {
        return $this->policies;
    }

    /**
     * @param array<int, mixed>|null $policies
     */
    public function setPolicies(?array $policies): void
    {
        $this->policies = $policies;
    }
}
