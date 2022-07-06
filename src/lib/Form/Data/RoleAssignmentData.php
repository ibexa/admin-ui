<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Form\Data;

class RoleAssignmentData
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[] */
    private $groups;

    /** @var \Ibexa\Contracts\Core\Repository\Values\User\User[] */
    private $users;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Section[] */
    private $sections;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location[] */
    private $locations;

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[] $groups
     */
    public function setGroups(array $groups)
    {
        $this->groups = $groups;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\User[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\User[] $users
     */
    public function setUsers(array $users)
    {
        $this->users = $users;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Section[]
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Section[] $sections
     */
    public function setSections(array $sections)
    {
        $this->sections = $sections;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
     */
    public function getLocations(): array
    {
        return $this->locations;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[] $locations
     */
    public function setLocations(array $locations)
    {
        $this->locations = $locations;
    }
}

class_alias(RoleAssignmentData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\RoleAssignmentData');
