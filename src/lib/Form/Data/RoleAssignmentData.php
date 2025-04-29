<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Data;

class RoleAssignmentData
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[]|null */
    private ?array $groups = null;

    /** @var \Ibexa\Contracts\Core\Repository\Values\User\User[]|null */
    private ?array $users = null;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Section[]|null */
    private ?array $sections = null;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location[]|null */
    private ?array $locations = null;

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[]|null
     */
    public function getGroups(): ?array
    {
        return $this->groups;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[]|null $groups
     */
    public function setGroups(?array $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\User[]|null
     */
    public function getUsers(): ?array
    {
        return $this->users;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\User[]|null $users
     */
    public function setUsers(?array $users): void
    {
        $this->users = $users;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Section[]|null
     */
    public function getSections(): ?array
    {
        return $this->sections;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Section[]|null $sections
     */
    public function setSections(?array $sections): void
    {
        $this->sections = $sections;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]|null
     */
    public function getLocations(): ?array
    {
        return $this->locations;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[]|null $locations
     */
    public function setLocations(?array $locations): void
    {
        $this->locations = $locations;
    }
}
