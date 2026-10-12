<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\Repository\Values\User\UserGroup;

final class RoleAssignmentData
{
    /** @var UserGroup[]|null */
    private ?array $groups = null;

    /** @var User[]|null */
    private ?array $users = null;

    /** @var Section[]|null */
    private ?array $sections = null;

    /** @var Location[]|null */
    private ?array $locations = null;

    /**
     * @return UserGroup[]|null
     */
    public function getGroups(): ?array
    {
        return $this->groups;
    }

    /**
     * @param UserGroup[]|null $groups
     */
    public function setGroups(?array $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * @return User[]|null
     */
    public function getUsers(): ?array
    {
        return $this->users;
    }

    /**
     * @param User[]|null $users
     */
    public function setUsers(?array $users): void
    {
        $this->users = $users;
    }

    /**
     * @return Section[]|null
     */
    public function getSections(): ?array
    {
        return $this->sections;
    }

    /**
     * @param Section[]|null $sections
     */
    public function setSections(?array $sections): void
    {
        $this->sections = $sections;
    }

    /**
     * @return Location[]|null
     */
    public function getLocations(): ?array
    {
        return $this->locations;
    }

    /**
     * @param Location[]|null $locations
     */
    public function setLocations(?array $locations): void
    {
        $this->locations = $locations;
    }
}
