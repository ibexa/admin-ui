<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Permission;

use Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
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
use Ibexa\Contracts\Core\Repository\Values\User\LookupLimitationResult;
use Ibexa\Contracts\Core\Repository\Values\User\Policy;
use Ibexa\Contracts\Core\Repository\Values\User\User;

class PermissionChecker implements PermissionCheckerInterface
{
    private const USER_GROUPS_LIMIT = 25;

    /** @var PermissionResolver */
    private $permissionResolver;

    /** @var UserService */
    private $userService;

    private LimitationResolverInterface $limitationResolver;

    public function __construct(
        PermissionResolver $permissionResolver,
        LimitationResolverInterface $limitationResolver,
        UserService $userService
    ) {
        $this->permissionResolver = $permissionResolver;
        $this->limitationResolver = $limitationResolver;
        $this->userService = $userService;
    }

    /**
     * @param $hasAccess
     * @param string $class
     *
     * @return array
     */
    public function getRestrictions(
        array $hasAccess,
        string $class
    ): array {
        $restrictions = [];
        $oneOfPoliciesHasNoLimitation = false;

        foreach ($this->flattenArrayOfLimitationsForCurrentUser($hasAccess) as $policy => $limitations) {
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
     * @param Location $location
     * @param array|bool $hasAccess
     *
     * @return bool
     *
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function canCreateInLocation(
        Location $location,
        $hasAccess
    ): bool {
        if (\is_bool($hasAccess)) {
            return $hasAccess;
        }
        $restrictedLocations = $this->getRestrictions($hasAccess, LocationLimitation::class);
        $canCreateInLocation = empty($restrictedLocations)
            ? true
            : \in_array($location->id, array_map('intval', $restrictedLocations), true);

        if (false === $canCreateInLocation) {
            return false;
        }

        $restrictedParentContentTypes = $this->getRestrictions($hasAccess, ParentContentTypeLimitation::class);
        $canCreateInParentContentType = empty($restrictedParentContentTypes)
            ? true
            : \in_array($location->contentInfo->contentTypeId, array_map('intval', $restrictedParentContentTypes), true);

        if (false === $canCreateInParentContentType) {
            return false;
        }

        $restrictedParentDepths = $this->getRestrictions($hasAccess, ParentDepthLimitation::class);
        $canCreateInParentDepth = empty($restrictedParentDepths)
            ? true
            : \in_array($location->depth, array_map('intval', $restrictedParentDepths), true);

        if (false === $canCreateInParentDepth) {
            return false;
        }

        $restrictedParentOwner = $this->getRestrictions($hasAccess, ParentOwnerLimitation::class);
        $canCreateInParentOwner = empty($restrictedParentOwner)
            ? true
            : $location->contentInfo->ownerId === $this->permissionResolver->getCurrentUserReference()->getUserId();

        if (false === $canCreateInParentOwner) {
            return false;
        }

        $restrictedSections = $this->getRestrictions($hasAccess, SectionLimitation::class);
        $canCreateInSection = empty($restrictedSections)
            ? true
            : \in_array($location->contentInfo->sectionId, array_map('intval', $restrictedSections), true);

        if (false === $canCreateInSection) {
            return false;
        }

        $restrictedParentUserGroups = $this->getRestrictions($hasAccess, ParentUserGroupLimitation::class);
        $canCreateInParentUserGroup = empty($restrictedParentUserGroups)
            ? true
            : $this->hasSameParentUserGroup($location);

        if (false === $canCreateInParentUserGroup) {
            return false;
        }

        $restrictedSubtrees = $this->getRestrictions($hasAccess, SubtreeLimitation::class);
        $canCreateInSubtree = empty($restrictedSubtrees)
            ? true
            : !empty(array_filter($restrictedSubtrees, static function ($restrictedSubtree) use ($location) {
                return strpos($location->pathString, $restrictedSubtree) === 0;
            }));

        if (false === $canCreateInSubtree) {
            return false;
        }

        return true;
    }

    public function getContentCreateLimitations(Location $parentLocation): LookupLimitationResult
    {
        trigger_deprecation(
            'ibexa/admin-ui',
            '4.6',
            sprintf('The %s() method is deprecated, will be removed in 5.0.', __METHOD__)
        );

        return $this->limitationResolver->getContentCreateLimitations($parentLocation);
    }

    public function getContentUpdateLimitations(Location $location): LookupLimitationResult
    {
        trigger_deprecation(
            'ibexa/admin-ui',
            '4.6',
            sprintf('The %s() method is deprecated, will be removed in 5.0.', __METHOD__)
        );

        return $this->limitationResolver->getContentUpdateLimitations($location);
    }

    /**
     * This method should only be used for very specific use cases. It should be used in a content cases
     * where assignment limitations are not relevant.
     *
     * @param array $hasAccess
     *
     * @return array
     */
    private function flattenArrayOfLimitationsForCurrentUser(array $hasAccess): array
    {
        $currentUserId = $this->permissionResolver->getCurrentUserReference()->getUserId();

        $limitations = [];
        foreach ($hasAccess as $permissionSet) {
            /** @var Policy $policy */
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
     * @param Location $location
     *
     * @return bool
     *
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    private function hasSameParentUserGroup(Location $location): bool
    {
        $currentUserId = $this->permissionResolver->getCurrentUserReference()->getUserId();
        $currentUser = $this->userService->loadUser($currentUserId);
        $currentUserGroups = $this->loadAllUserGroupsIdsOfUser($currentUser);

        $locationOwnerId = $location->contentInfo->ownerId;
        $locationOwner = $this->userService->loadUser($locationOwnerId);
        $locationOwnerGroups = $this->loadAllUserGroupsIdsOfUser($locationOwner);

        return !empty(array_intersect($currentUserGroups, $locationOwnerGroups));
    }

    /**
     * @param User $user
     *
     * @return int[]
     *
     * @throws UnauthorizedException
     */
    private function loadAllUserGroupsIdsOfUser(User $user): array
    {
        $allUserGroups = [];
        $offset = 0;

        do {
            $userGroups = $this->userService->loadUserGroupsOfUser($user, $offset, self::USER_GROUPS_LIMIT);
            foreach ($userGroups as $userGroup) {
                $allUserGroups[] = $userGroup->contentInfo->id;
            }
            $offset += self::USER_GROUPS_LIMIT;
        } while (\count($userGroups) === self::USER_GROUPS_LIMIT);

        return $allUserGroups;
    }
}

class_alias(PermissionChecker::class, 'EzSystems\EzPlatformAdminUi\Permission\PermissionChecker');
