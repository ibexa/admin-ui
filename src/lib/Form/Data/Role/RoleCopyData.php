<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Role;

use Ibexa\Contracts\Core\Repository\Values\User\Role;

class RoleCopyData
{
    private Role $copiedRole;

    private ?string $newIdentifier;

    public function __construct(Role $role, ?string $identifier = null)
    {
        $this->copiedRole = $role;
        $this->newIdentifier = $identifier;
    }

    public function getCopiedRole(): ?Role
    {
        return $this->copiedRole;
    }

    public function setCopiedRole(Role $role): self
    {
        $this->copiedRole = $role;

        return $this;
    }

    public function getNewIdentifier(): ?string
    {
        return $this->newIdentifier;
    }

    public function setNewIdentifier(?string $identifier): self
    {
        $this->newIdentifier = $identifier;

        return $this;
    }
}
