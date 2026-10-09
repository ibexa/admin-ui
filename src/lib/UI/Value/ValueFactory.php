<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Value;

use Ibexa\AdminUi\Specification\UserExists;
use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\AdminUi\UI\Service\PathService;
use Ibexa\AdminUi\UI\Value as UIValue;
use Ibexa\AdminUi\UI\Value\Content\ContentDraftInterface;
use Ibexa\AdminUi\UI\Value\Content\RelationInterface;
use Ibexa\AdminUi\UI\Value\Location\Bookmark;
use Ibexa\AdminUi\UI\Value\User\Role;
use Ibexa\Contracts\Core\Limitation\Target;
use Ibexa\Contracts\Core\Limitation\Target\Builder\VersionBuilder;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\ForbiddenException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\DraftList\Item\ContentDraftListItem;
use Ibexa\Contracts\Core\Repository\Values\Content\DraftList\Item\UnauthorizedContentDraftListItem;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Relation;
use Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\RelationListItem;
use Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\UnauthorizedRelationListItem;
use Ibexa\Contracts\Core\Repository\Values\Content\URLAlias;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Ibexa\Contracts\Core\Repository\Values\User\Policy;
use Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Ibexa\Core\Repository\LocationResolver\LocationResolver;

class ValueFactory
{
    /** @var UserService */
    protected $userService;

    /** @var LanguageService */
    protected $languageService;

    /** @var LocationService */
    protected $locationService;

    /** @var ContentTypeService */
    protected $contentTypeService;

    /** @var SearchService */
    protected $searchService;

    /** @var ObjectStateService */
    protected $objectStateService;

    /** @var PermissionResolver */
    protected $permissionResolver;

    /** @var DatasetFactory */
    protected $datasetFactory;

    /** @var PathService */
    protected $pathService;

    /** @var UserLanguagePreferenceProviderInterface */
    private $userLanguagePreferenceProvider;

    /** @var LocationResolver */
    protected $locationResolver;

    /**
     * @param UserService $userService
     * @param LanguageService $languageService
     * @param LocationService $locationService
     * @param ContentTypeService $contentTypeService
     * @param SearchService $searchService
     * @param ObjectStateService $objectStateService
     * @param PermissionResolver $permissionResolver
     * @param PathService $pathService
     * @param DatasetFactory $datasetFactory
     * @param UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider
     * @param LocationResolver $locationResolver
     */
    public function __construct(
        UserService $userService,
        LanguageService $languageService,
        LocationService $locationService,
        ContentTypeService $contentTypeService,
        SearchService $searchService,
        ObjectStateService $objectStateService,
        PermissionResolver $permissionResolver,
        PathService $pathService,
        DatasetFactory $datasetFactory,
        UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        LocationResolver $locationResolver
    ) {
        $this->userService = $userService;
        $this->languageService = $languageService;
        $this->locationService = $locationService;
        $this->contentTypeService = $contentTypeService;
        $this->searchService = $searchService;
        $this->objectStateService = $objectStateService;
        $this->permissionResolver = $permissionResolver;
        $this->pathService = $pathService;
        $this->datasetFactory = $datasetFactory;
        $this->userLanguagePreferenceProvider = $userLanguagePreferenceProvider;
        $this->locationResolver = $locationResolver;
    }

    /**
     * @param VersionInfo $versionInfo
     *
     * @return VersionInfo
     *
     * @throws BadStateException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function createVersionInfo(VersionInfo $versionInfo): UIValue\Content\VersionInfo
    {
        $translationsDataset = $this->datasetFactory->translations();
        $translationsDataset->load($versionInfo);

        $author = (new UserExists($this->userService))->isSatisfiedBy($versionInfo->creatorId)
            ? $this->userService->loadUser($versionInfo->creatorId) : null;

        return new UIValue\Content\VersionInfo($versionInfo, [
            'author' => $author,
            'translations' => $translationsDataset->getTranslations(),
            'userCanRemove' => $this->permissionResolver->canUser(
                'content',
                'versionremove',
                $versionInfo
            ),
        ]);
    }

    /**
     * @param Language $language
     * @param VersionInfo $versionInfo
     *
     * @return Language
     *
     * @throws BadStateException
     * @throws InvalidArgumentException
     */
    public function createLanguage(
        Language $language,
        VersionInfo $versionInfo
    ): UIValue\Content\Language {
        $target = (new VersionBuilder())->translateToAnyLanguageOf([$language->languageCode])->build();

        return new UIValue\Content\Language($language, [
            'userCanRemove' => $this->permissionResolver->canUser('content', 'remove', $versionInfo, [$target]),
            'userCanEdit' => $this->permissionResolver->canUser('content', 'edit', $versionInfo),
            'main' => $language->languageCode === $versionInfo->getContentInfo()->mainLanguageCode,
        ]);
    }

    /**
     * @deprecated since version 2.5, to be removed in 3.0. Please use ValueFactory::createRelationItem instead.
     *
     * @param Relation $relation
     * @param Content $content
     *
     * @return Relation
     *
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws ForbiddenException
     * @throws BadStateException
     */
    public function createRelation(
        Relation $relation,
        Content $content
    ): UIValue\Content\Relation {
        $contentType = $content->getContentType();

        return new UIValue\Content\Relation($relation, [
            'relationFieldDefinitionName' => $this->getRelationFieldDefinitionName($relation, $contentType),
            'relationContentTypeName' => $contentType->getName(),
            'relationLocation' => $this->locationResolver->resolveLocation($content->contentInfo),
            'relationName' => $content->getName(),
            'resolvedSourceLocation' => $this->locationResolver->resolveLocation($relation->sourceContentInfo),
            'resolvedDestinationLocation' => $this->locationResolver->resolveLocation($relation->destinationContentInfo),
        ]);
    }

    /**
     * @param RelationListItem $relationListItem
     * @param Content $content
     *
     * @return Relation
     *
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws ForbiddenException
     * @throws BadStateException
     */
    public function createRelationItem(
        RelationListItem $relationListItem,
        Content $content
    ): UIValue\Content\Relation {
        $contentType = $content->getContentType();
        $relation = $relationListItem->getRelation();

        return new UIValue\Content\Relation($relation, [
            'relationFieldDefinitionName' => $this->getRelationFieldDefinitionName($relation, $contentType),
            'relationContentTypeName' => $contentType->getName(),
            'relationLocation' => $this->locationResolver->resolveLocation($content->contentInfo),
            'relationName' => $content->getName(),
            'resolvedSourceLocation' => $this->locationResolver->resolveLocation($relation->sourceContentInfo),
            'resolvedDestinationLocation' => $this->locationResolver->resolveLocation($relation->destinationContentInfo),
        ]);
    }

    /**
     * @param UnauthorizedRelationListItem $relationListItem
     *
     * @return RelationInterface
     */
    public function createUnauthorizedRelationItem(
        UnauthorizedRelationListItem $relationListItem
    ): RelationInterface {
        return new UIValue\Content\UnauthorizedRelation($relationListItem);
    }

    /**
     * @param Location $location
     *
     * @return Location
     *
     * @throws BadStateException
     * @throws InvalidArgumentException
     */
    public function createLocation(Location $location): UIValue\Content\Location
    {
        $translations = $location->getContent()->getVersionInfo()->languageCodes;
        $target = (new Target\Version())->deleteTranslations($translations);

        return new UIValue\Content\Location($location, [
            'childCount' => $this->locationService->getLocationChildCount($location),
            'pathLocations' => $this->pathService->loadPathLocations($location),
            'userCanManage' => $this->permissionResolver->canUser(
                'content',
                'manage_locations',
                $location->getContentInfo()
            ),
            'userCanRemove' => $this->permissionResolver->canUser(
                'content',
                'remove',
                $location->getContentInfo(),
                [$location, $target]
            ),
            'userCanEdit' => $this->permissionResolver->canUser(
                'content',
                'edit',
                $location->getContentInfo(),
                [$location]
            ),
            'main' => $location->getContentInfo()->mainLocationId === $location->id,
        ]);
    }

    /**
     * @param ContentInfo $contentInfo
     * @param ObjectStateGroup $objectStateGroup
     *
     * @return ObjectState\ObjectState
     *
     * @throws BadStateException
     * @throws InvalidArgumentException
     */
    public function createObjectState(
        ContentInfo $contentInfo,
        ObjectStateGroup $objectStateGroup
    ): ObjectState\ObjectState {
        $objectState = $this->objectStateService->getContentState($contentInfo, $objectStateGroup);

        return new ObjectState\ObjectState($objectState, [
            'userCanAssign' => $this->permissionResolver->canUser('state', 'assign', $contentInfo, [$objectState]),
        ]);
    }

    /**
     * @param URLAlias $urlAlias
     *
     * @return UIValue\Content\UrlAlias
     */
    public function createUrlAlias(URLAlias $urlAlias): UIValue\Content\UrlAlias
    {
        return new UIValue\Content\UrlAlias($urlAlias);
    }

    /**
     * @param RoleAssignment $roleAssignment
     *
     * @return Role
     */
    public function createRole(RoleAssignment $roleAssignment): Role
    {
        return new Role($roleAssignment);
    }

    /**
     * @param Policy $policy
     * @param RoleAssignment $roleAssignment
     *
     * @return User\Policy
     */
    public function createPolicy(
        Policy $policy,
        RoleAssignment $roleAssignment
    ): User\Policy {
        return new User\Policy($policy, ['role_assignment' => $roleAssignment]);
    }

    /**
     * @param Location $location
     *
     * @return Bookmark
     *
     * @throws BadStateException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function createBookmark(Location $location): Bookmark
    {
        return new Bookmark(
            $location,
            [
                'contentType' => $this->contentTypeService->loadContentType(
                    $location->getContentInfo()->contentTypeId,
                    $this->userLanguagePreferenceProvider->getPreferredLanguages()
                ),
                'pathLocations' => $this->pathService->loadPathLocations($location),
                'userCanEdit' => $this->permissionResolver->canUser('content', 'edit', $location->contentInfo),
            ]
        );
    }

    /**
     * @param Language $language
     * @param ContentType $contentType
     *
     * @return UIValue\Content\Language
     *
     * @throws BadStateException
     * @throws InvalidArgumentException
     */
    public function createLanguageFromContentType(
        Language $language,
        ContentType $contentType
    ): UIValue\Content\Language {
        return new UIValue\Content\Language($language, [
            'userCanRemove' => $this->permissionResolver->canUser('class', 'update', $contentType),
            'main' => $language->languageCode === $contentType->mainLanguageCode,
        ]);
    }

    /**
     * @param ContentDraftListItem $contentDraftListItem
     * @param ContentType $contentType
     *
     * @return ContentDraftInterface
     */
    public function createContentDraft(
        ContentDraftListItem $contentDraftListItem,
        ContentType $contentType
    ): ContentDraftInterface {
        $versionInfo = $contentDraftListItem->getVersionInfo();
        $contentInfo = $versionInfo->contentInfo;
        $versionId = new UIValue\Content\VersionId(
            $contentInfo->id,
            $versionInfo->versionNo
        );

        return new UIValue\Content\ContentDraft(
            $versionInfo,
            $versionId,
            $contentType
        );
    }

    /**
     * @param UnauthorizedContentDraftListItem $contentDraftListItem
     *
     * @return ContentDraftInterface
     */
    public function createUnauthorizedContentDraft(
        UnauthorizedContentDraftListItem $contentDraftListItem
    ): ContentDraftInterface {
        return new UIValue\Content\UnauthorizedContentDraft($contentDraftListItem);
    }

    private function getRelationFieldDefinitionName(
        ?Relation $relation,
        ContentType $contentType
    ): string {
        if ($relation !== null && $relation->sourceFieldDefinitionIdentifier !== null) {
            $fieldDefinition = $contentType->getFieldDefinition(
                $relation->sourceFieldDefinitionIdentifier
            );

            if ($fieldDefinition !== null) {
                return $fieldDefinition->getName();
            }
        }

        return '';
    }
}

class_alias(ValueFactory::class, 'EzSystems\EzPlatformAdminUi\UI\Value\ValueFactory');
