<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Data\Role;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RoleAssignmentCreateData implements TranslationContainerInterface
{
    public const LIMITATION_TYPE_NONE = 'none';
    public const LIMITATION_TYPE_SECTION = 'section';
    public const LIMITATION_TYPE_LOCATION = 'location';

    /** @var \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[] */
    private $groups;

    /** @var \Ibexa\Contracts\Core\Repository\Values\User\User[] */
    private $users;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Section[]
     *
     * @Assert\Expression(
     *     "this.getLimitationType() != 'section' or (this.getLimitationType() == 'section' and value != [])",
     *     message="validator.define_subtree_or_section_limitation"
     * )
     */
    private $sections;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
     *
     * @Assert\Expression(
     *     "this.getLimitationType() != 'location' or (this.getLimitationType() == 'location' and value != [])",
     *     message="validator.define_subtree_or_section_limitation"
     * )
     */
    private $locations;

    /**
     * @var string
     *
     * @Assert\NotNull()
     * @Assert\Choice({
     *     RoleAssignmentCreateData::LIMITATION_TYPE_NONE,
     *     RoleAssignmentCreateData::LIMITATION_TYPE_SECTION,
     *     RoleAssignmentCreateData::LIMITATION_TYPE_LOCATION
     * })
     */
    private $limitationType;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[] $groups
     * @param \Ibexa\Contracts\Core\Repository\Values\User\User[] $users
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Section[] $sections
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[] $locations
     * @param string $limitationType
     */
    public function __construct(
        array $groups = [],
        array $users = [],
        array $sections = [],
        array $locations = [],
        $limitationType = self::LIMITATION_TYPE_NONE
    ) {
        $this->groups = $groups;
        $this->users = $users;
        $this->sections = $sections;
        $this->locations = $locations;
        $this->limitationType = $limitationType;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[]
     */
    public function getGroups(): ?array
    {
        return $this->groups;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[] $groups
     *
     * @return self
     */
    public function setGroups(array $groups): self
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\User[]
     */
    public function getUsers(): ?array
    {
        return $this->users;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\User[] $users
     *
     * @return self
     */
    public function setUsers(array $users): self
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Section[]
     */
    public function getSections(): ?array
    {
        return $this->sections;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Section[] $sections
     *
     * @return self
     */
    public function setSections(array $sections): self
    {
        $this->sections = $sections;

        return $this;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location[]
     */
    public function getLocations(): ?array
    {
        return $this->locations;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location[] $locations
     *
     * @return self
     */
    public function setLocations(array $locations): self
    {
        $this->locations = $locations;

        return $this;
    }

    /**
     * @return string
     */
    public function getLimitationType(): string
    {
        return $this->limitationType;
    }

    /**
     * @param string $limitationType
     *
     * @return self
     */
    public function setLimitationType(string $limitationType): self
    {
        $this->limitationType = $limitationType;

        return $this;
    }

    /**
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     * @param $payload
     *
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if (empty($this->getUsers()) && empty($this->getGroups())) {
            $context->buildViolation(
                'validator.assign_users_or_groups'
            )
                ->setTranslationDomain('ibexa_role')
                ->addViolation();
        }
    }

    public static function getTranslationMessages()
    {
        return [
            Message::create('validator.assign_users_or_groups', 'ibexa_role')
                ->setDesc('Assign User(s) and/or Group(s) to the Role'),
            Message::create('validator.define_subtree_or_section_limitation', 'validators')
                ->setDesc('Define a Subtree or Section limitation'),
        ];
    }
}

class_alias(RoleAssignmentCreateData::class, 'EzSystems\EzPlatformAdminUi\Form\Data\Role\RoleAssignmentCreateData');
