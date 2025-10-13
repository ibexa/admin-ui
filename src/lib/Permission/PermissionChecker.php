<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Permission;

use Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\LocationLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ParentContentTypeLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ParentDepthLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ParentOwnerLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ParentUserGroupLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SectionLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SubtreeLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\User;

final readonly class PermissionChecker implements PermissionCheckerInterface
{
    private const int USER_GROUPS_LIMIT = 25;

    public function __construct(
        private PermissionResolver $permissionResolver,
        private UserService $userService
    ) {
    }

    /**
     * @param mixed[] $hasAccess
     * @param string $class
     *
     * @return mixed[]
     */
    public function getRestrictions(array $hasAccess, string $class): array
    {
        $restrictions = [];
        $oneOfPoliciesHasNoLimitation = false;

        foreach ($this->flattenArrayOfLimitationsForCurrentUser($hasAccess) as $limitations) {
            $policyHasLimitation = false;
            foreach ($limitations as $limitation) {
                if ($limitation instanceof $class) {
                    $restrictions[] = $limitation->limitationValues;
                    $policyHasLimitation = true;
                }
            }
            if (false === $policyHasLimitation) {
                $oneOfPoliciesHasNoLimitation = true;
            }
        }

        if ($oneOfPoliciesHasNoLimitation) {
            return [];
        }

        return empty($restrictions) ? $restrictions : array_unique(array_merge(...$restrictions));
    }

    /**
     * @param array<mixed>|bool $hasAccess
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function canCreateInLocation(Location $location, array|bool $hasAccess): bool
    {
        if (is_bool($hasAccess)) {
            return $hasAccess;
        }
        $restrictedLocations = $this->getRestrictions($hasAccess, LocationLimitation::class);
        $canCreateInLocation = empty($restrictedLocations) || in_array($location->getId(), array_map('intval', $restrictedLocations), true);

        if (false === $canCreateInLocation) {
            return false;
        }

        $restrictedParentContentTypes = $this->getRestrictions($hasAccess, ParentContentTypeLimitation::class);
        $canCreateInParentContentType = empty($restrictedParentContentTypes) || in_array($location->getContentInfo()->contentTypeId, array_map('intval', $restrictedParentContentTypes), true);

        if (false === $canCreateInParentContentType) {
            return false;
        }

        $restrictedParentDepths = $this->getRestrictions($hasAccess, ParentDepthLimitation::class);
        $canCreateInParentDepth = empty($restrictedParentDepths) || in_array($location->getDepth(), array_map('intval', $restrictedParentDepths), true);

        if (false === $canCreateInParentDepth) {
            return false;
        }

        $restrictedParentOwner = $this->getRestrictions($hasAccess, ParentOwnerLimitation::class);
        $canCreateInParentOwner = empty($restrictedParentOwner) || $location->getContentInfo()->ownerId === $this->permissionResolver->getCurrentUserReference()->getUserId();

        if (false === $canCreateInParentOwner) {
            return false;
        }

        $restrictedSections = $this->getRestrictions($hasAccess, SectionLimitation::class);
        $canCreateInSection = empty($restrictedSections) || in_array($location->getContentInfo()->getSectionId(), array_map('intval', $restrictedSections), true);

        if (false === $canCreateInSection) {
            return false;
        }

        $restrictedParentUserGroups = $this->getRestrictions($hasAccess, ParentUserGroupLimitation::class);
        $canCreateInParentUserGroup = empty($restrictedParentUserGroups) || $this->hasSameParentUserGroup($location);

        if (false === $canCreateInParentUserGroup) {
            return false;
        }

        $restrictedSubtrees = $this->getRestrictions($hasAccess, SubtreeLimitation::class);
        $canCreateInSubtree = empty($restrictedSubtrees) || !empty(array_filter($restrictedSubtrees, static function ($restrictedSubtree) use ($location): bool {
                return str_starts_with($location->getPathString(), $restrictedSubtree);
            }));

        if (false === $canCreateInSubtree) {
            return false;
        }

        return true;
    }

    /**
     * This method should only be used for very specific use cases. It should be used in a content cases
     * where assignment limitations are not relevant.
     *
     * @param mixed[] $hasAccess
     *
     * @return array<int, mixed>
     */
    private function flattenArrayOfLimitationsForCurrentUser(array $hasAccess): array
    {
        $limitations = [];
        foreach ($hasAccess as $permissionSet) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\User\Policy $policy */
            foreach ($permissionSet['policies'] as $policy) {
                $policyLimitations = $policy->getLimitations();
                if (!empty($policyLimitations)) {
                    foreach ($policyLimitations as $policyLimitation) {
                        $limitations[$policy->id][] = $policyLimitation;
                    }
                }
                if ($permissionSet['limitation'] !== null) {
                    $limitations[$policy->id][] = $permissionSet['limitation'];
                }
            }
        }

        return $limitations;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function hasSameParentUserGroup(Location $location): bool
    {
        $currentUserId = $this->permissionResolver->getCurrentUserReference()->getUserId();
        $currentUser = $this->userService->loadUser($currentUserId);
        $currentUserGroups = $this->loadAllUserGroupsIdsOfUser($currentUser);

        $locationOwnerId = $location->getContentInfo()->ownerId;
        $locationOwner = $this->userService->loadUser($locationOwnerId);
        $locationOwnerGroups = $this->loadAllUserGroupsIdsOfUser($locationOwner);

        return !empty(array_intersect($currentUserGroups, $locationOwnerGroups));
    }

    /**
     * @return int[]
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function loadAllUserGroupsIdsOfUser(User $user): array
    {
        $allUserGroups = [];
        $offset = 0;

        do {
            $userGroups = $this->userService->loadUserGroupsOfUser($user, $offset, self::USER_GROUPS_LIMIT);
            foreach ($userGroups as $userGroup) {
                $allUserGroups[] = $userGroup->getContentInfo()->getId();
            }
            $offset += self::USER_GROUPS_LIMIT;
        } while (count($userGroups) === self::USER_GROUPS_LIMIT);

        return $allUserGroups;
    }
}
