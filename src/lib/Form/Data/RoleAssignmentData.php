<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Form\Data;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\Repository\Values\User\UserGroup;

class RoleAssignmentData
{
    /** @var UserGroup[] */
    private $groups;

    /** @var User[] */
    private $users;

    /** @var Section[] */
    private $sections;

    /** @var Location[] */
    private $locations;

    /**
     * @return UserGroup[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @param UserGroup[] $groups
     */
    public function setGroups(array $groups)
    {
        $this->groups = $groups;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @param User[] $users
     */
    public function setUsers(array $users)
    {
        $this->users = $users;
    }

    /**
     * @return Section[]
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * @param Section[] $sections
     */
    public function setSections(array $sections)
    {
        $this->sections = $sections;
    }

    /**
     * @return Location[]
     */
    public function getLocations(): array
    {
        return $this->locations;
    }

    /**
     * @param Location[] $locations
     */
    public function setLocations(array $locations)
    {
        $this->locations = $locations;
    }
}

class_alias(RoleAssignmentData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\RoleAssignmentData');
