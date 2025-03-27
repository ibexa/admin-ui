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
use Ibexa\Contracts\Core\Repository\Values\User\Policy;

class PoliciesDataset
{
    private RoleService $roleService;

    private ContentService $contentService;

    private ContentTypeService $contentTypeService;

    private UserService $userService;

    protected ValueFactory $valueFactory;

    private array $userContentTypeIdentifier;

    private array $userGroupContentTypeIdentifier;

    /** @var \Ibexa\AdminUi\UI\Value\Content\UrlAlias[] */
    private ?array $data = null;

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
     * @return \Ibexa\AdminUi\UI\Dataset\PoliciesDataset
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function load(Location $location): self
    {
        $roleAssignments = [];
        $content = $location->getContent();
        $contentType = $content->getContentType();

        if ((new ContentTypeIsUser($this->userContentTypeIdentifier))->isSatisfiedBy($contentType)) {
            $user = $this->userService->loadUser($content->id);
            $roleAssignments = $this->roleService->getRoleAssignmentsForUser($user, true);
        }

        if ((new ContentTypeIsUserGroup($this->userGroupContentTypeIdentifier))->isSatisfiedBy($contentType)) {
            $userGroup = $this->userService->loadUserGroup($content->id);
            $roleAssignments = $this->roleService->getRoleAssignmentsForUserGroup($userGroup);
        }

        $this->data = [];
        $policies = [[]];

        foreach ($roleAssignments as $roleAssignment) {
            $policies[] = array_map(
                function (Policy $policy) use ($roleAssignment) {
                    return $this->valueFactory->createPolicy($policy, $roleAssignment);
                },
                $roleAssignment->getRole()->getPolicies()
            );
        }

        $this->data = array_merge(...$policies);

        return $this;
    }

    /**
     * @return \Ibexa\AdminUi\UI\Value\User\Policy[]
     */
    public function getPolicies(): array
    {
        return $this->data;
    }
}
