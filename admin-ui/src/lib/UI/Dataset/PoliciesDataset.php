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
use Ibexa\Contracts\Core\Repository\Values\User\Policy;

class PoliciesDataset
{
    private RoleService $roleService;

    private UserService $userService;

    protected ValueFactory $valueFactory;

    /** @var string[] */
    private array $userContentTypeIdentifier;

    /** @var string[] */
    private array $userGroupContentTypeIdentifier;

    /** @var \Ibexa\AdminUi\UI\Value\User\Policy[]|null */
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
        return $this->data ?? [];
    }
}
