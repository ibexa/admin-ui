<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser;
use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUserGroup;
use Ibexa\AdminUi\UI\Value as UIValue;
use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\User\Policy;

final class PoliciesDataset
{
    /** @var \Ibexa\AdminUi\UI\Value\User\Policy[]|null */
    private ?array $data = null;

    /**
     * @param string[] $userContentTypeIdentifier
     * @param string[] $userGroupContentTypeIdentifier
     */
    public function __construct(
        private readonly RoleService $roleService,
        private readonly UserService $userService,
        private readonly ValueFactory $valueFactory,
        private readonly array $userContentTypeIdentifier,
        private readonly array $userGroupContentTypeIdentifier
    ) {
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
            $user = $this->userService->loadUser($content->getId());
            $roleAssignments = $this->roleService->getRoleAssignmentsForUser($user, true);
        }

        if ((new ContentTypeIsUserGroup($this->userGroupContentTypeIdentifier))->isSatisfiedBy($contentType)) {
            $userGroup = $this->userService->loadUserGroup($content->getId());
            $roleAssignments = $this->roleService->getRoleAssignmentsForUserGroup($userGroup);
        }

        $this->data = [];
        $policies = [[]];

        foreach ($roleAssignments as $roleAssignment) {
            $policies[] = array_map(
                function (Policy $policy) use ($roleAssignment): UIValue\User\Policy {
                    return $this->valueFactory->createPolicy($policy, $roleAssignment);
                },
                iterator_to_array($roleAssignment->getRole()->getPolicies())
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
