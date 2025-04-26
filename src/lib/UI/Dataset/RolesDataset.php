<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser;
use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUserGroup;
use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

class RolesDataset
{
    private RoleService $roleService;

    private UserService $userService;

    protected ValueFactory $valueFactory;

    /** @var string[] */
    private array $userContentTypeIdentifier;

    /** @var string[] */
    private array $userGroupContentTypeIdentifier;

    /** @var \Ibexa\AdminUi\UI\Value\User\Role[]|null */
    private ?array $data = null;

    /**
     * @param string[] $userContentTypeIdentifier
     * @param string[] $userGroupContentTypeIdentifier
     */
    public function __construct(
        RoleService $roleService,
        UserService $userService,
        ValueFactory $valueFactory,
        array $userContentTypeIdentifier,
        array $userGroupContentTypeIdentifier
    ) {
        $this->roleService = $roleService;
        $this->userService = $userService;
        $this->valueFactory = $valueFactory;
        $this->userContentTypeIdentifier = $userContentTypeIdentifier;
        $this->userGroupContentTypeIdentifier = $userGroupContentTypeIdentifier;
    }

    /**
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function load(Location $location): self
    {
        $roleAssignment = [];
        $content = $location->getContent();
        $contentType = $content->getContentType();

        // @todo $content should just have been instance of User or UserGroup direclty so we don't need to re-load data
        if ((new ContentTypeIsUser($this->userContentTypeIdentifier))->isSatisfiedBy($contentType)) {
            $user = $this->userService->loadUser($content->id);
            $roleAssignment = $this->roleService->getRoleAssignmentsForUser($user, true);
        }

        if ((new ContentTypeIsUserGroup($this->userGroupContentTypeIdentifier))->isSatisfiedBy($contentType)) {
            $userGroup = $this->userService->loadUserGroup($content->id);
            $roleAssignment = $this->roleService->getRoleAssignmentsForUserGroup($userGroup);
        }

        $this->data = array_map(
            [$this->valueFactory, 'createRole'],
            $roleAssignment
        );

        return $this;
    }

    /**
     * @return \Ibexa\AdminUi\UI\Value\User\Role[]
     */
    public function getRoles(): array
    {
        return $this->data ?? [];
    }
}
