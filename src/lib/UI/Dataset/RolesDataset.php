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
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

class RolesDataset
{
    /** @var \Ibexa\Contracts\Core\Repository\RoleService */
    private $roleService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Ibexa\Contracts\Core\Repository\UserService */
    private $userService;

    /** @var \Ibexa\AdminUi\UI\Value\ValueFactory */
    protected $valueFactory;

    /** @var array */
    private $userContentTypeIdentifier;

    /** @var array */
    private $userGroupContentTypeIdentifier;

    /** @var \Ibexa\AdminUi\UI\Value\Content\UrlAlias[] */
    private $data;

    /**
     * @param \Ibexa\Contracts\Core\Repository\RoleService $roleService
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     * @param \Ibexa\Contracts\Core\Repository\UserService $userService
     * @param \Ibexa\AdminUi\UI\Value\ValueFactory $valueFactory
     * @param array $userContentTypeIdentifier
     * @param array $userGroupContentTypeIdentifier
     */
    public function __construct(
        RoleService $roleService,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        UserService $userService,
        ValueFactory $valueFactory,
        array $userContentTypeIdentifier,
        array $userGroupContentTypeIdentifier
    ) {
        $this->roleService = $roleService;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->userService = $userService;
        $this->valueFactory = $valueFactory;
        $this->userContentTypeIdentifier = $userContentTypeIdentifier;
        $this->userGroupContentTypeIdentifier = $userGroupContentTypeIdentifier;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     *
     * @return \Ibexa\AdminUi\UI\Dataset\RolesDataset
     *
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
        return $this->data;
    }
}
