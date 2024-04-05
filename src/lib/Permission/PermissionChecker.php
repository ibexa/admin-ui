<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Permission;

use Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface;
use Ibexa\Contracts\Core\Limitation\Target\Builder\VersionBuilder;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\LocationLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ParentContentTypeLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ParentDepthLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ParentOwnerLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ParentUserGroupLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SectionLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SubtreeLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\LookupLimitationResult;
use Ibexa\Contracts\Core\Repository\Values\User\User;

class PermissionChecker implements PermissionCheckerInterface
{
    private const USER_GROUPS_LIMIT = 25;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \Ibexa\Contracts\Core\Repository\UserService */
    private $userService;

    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $locationService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /** @var \Ibexa\Contracts\Core\Repository\LanguageService */
    private $languageService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\PermissionResolver $permissionResolver
     * @param \Ibexa\Contracts\Core\Repository\UserService $userService
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     * @param \Ibexa\Contracts\Core\Repository\LanguageService $languageService
     */
    public function __construct(
        PermissionResolver $permissionResolver,
        UserService $userService,
        LocationService $locationService,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        LanguageService $languageService
    ) {
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
        $this->locationService = $locationService;
        $this->contentTypeService = $contentTypeService;
        $this->contentService = $contentService;
        $this->languageService = $languageService;
    }

    /**
     * @param $hasAccess
     * @param string $class
     *
     * @return array
     */
    public function getRestrictions(array $hasAccess, string $class): array
    {
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     * @param array|bool $hasAccess
     *
     * @return bool
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function canCreateInLocation(Location $location, $hasAccess): bool
    {
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
        $contentType = $this->contentTypeService->loadContentType($parentLocation->contentInfo->contentTypeId);
        $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, $parentLocation->contentInfo->mainLanguageCode);
        $contentCreateStruct->sectionId = $parentLocation->contentInfo->sectionId;
        $locationCreateStruct = $this->locationService->newLocationCreateStruct($parentLocation->id);

        $versionBuilder = new VersionBuilder();
        $versionBuilder->translateToAnyLanguageOf($this->getActiveLanguageCodes());
        $versionBuilder->createFromAnyContentTypeOf($this->getContentTypeIds());

        return $this->permissionResolver->lookupLimitations(
            'content',
            'create',
            $contentCreateStruct,
            [$versionBuilder->build(), $locationCreateStruct],
            [Limitation::CONTENTTYPE, Limitation::LANGUAGE]
        );
    }

    public function getContentUpdateLimitations(Location $location): LookupLimitationResult
    {
        $contentInfo = $location->getContentInfo();

        $versionBuilder = new VersionBuilder();
        $versionBuilder->translateToAnyLanguageOf($this->getActiveLanguageCodes());
        $versionBuilder->createFromAnyContentTypeOf($this->getContentTypeIds());

        return $this->permissionResolver->lookupLimitations(
            'content',
            'edit',
            $contentInfo,
            [$versionBuilder->build(), $location],
            [Limitation::CONTENTTYPE, Limitation::LANGUAGE]
        );
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     *
     * @return bool
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
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
     * @param \Ibexa\Contracts\Core\Repository\Values\User\User $user
     *
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
                $allUserGroups[] = $userGroup->contentInfo->id;
            }
            $offset += self::USER_GROUPS_LIMIT;
        } while (\count($userGroups) === self::USER_GROUPS_LIMIT);

        return $allUserGroups;
    }

    /**
     * @return string[]
     */
    private function getActiveLanguageCodes(): array
    {
        $filter = array_filter(
            $this->languageService->loadLanguages(),
            static function (Language $language) {
                return $language->enabled;
            }
        );

        return array_column($filter, 'languageCode');
    }

    /**
     * @return string[]
     */
    private function getContentTypeIds(): array
    {
        $contentTypeIds = [];

        $contentTypeGroups = $this->contentTypeService->loadContentTypeGroups();
        foreach ($contentTypeGroups as $contentTypeGroup) {
            $contentTypes = $this->contentTypeService->loadContentTypes($contentTypeGroup);
            foreach ($contentTypes as $contentType) {
                $contentTypeIds[] = $contentType->id;
            }
        }

        return $contentTypeIds;
    }
}

class_alias(PermissionChecker::class, 'EzSystems\EzPlatformAdminUi\Permission\PermissionChecker');
