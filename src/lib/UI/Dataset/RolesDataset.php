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
    /** @var \Ibexa\AdminUi\UI\Value\User\Role[]|null */
    private ?array $data = null;

    /**
     * @param string[] $userContentTypeIdentifier
     * @param string[] $userGroupContentTypeIdentifier
     */
    public function __construct(
        private readonly RoleService $roleService,
        private readonly UserService $userService,
        protected readonly ValueFactory $valueFactory,
        private readonly array $userContentTypeIdentifier,
        private readonly array $userGroupContentTypeIdentifier
    ) {
    }

    /**
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
            $user = $this->userService->loadUser($content->getId());
            $roleAssignment = $this->roleService->getRoleAssignmentsForUser($user, true);
        }

        if ((new ContentTypeIsUserGroup($this->userGroupContentTypeIdentifier))->isSatisfiedBy($contentType)) {
            $userGroup = $this->userService->loadUserGroup($content->getId());
            $roleAssignment = $this->roleService->getRoleAssignmentsForUserGroup($userGroup);
        }

        $this->data = array_map(
            [$this->valueFactory, 'createRole'],
            iterator_to_array($roleAssignment)
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
