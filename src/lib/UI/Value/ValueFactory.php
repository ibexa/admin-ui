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
use Ibexa\Contracts\Core\Limitation\Target;
use Ibexa\Contracts\Core\Limitation\Target\Builder\VersionBuilder;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
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
    /** @var \Ibexa\Contracts\Core\Repository\UserService */
    protected $userService;

    /** @var \Ibexa\Contracts\Core\Repository\LanguageService */
    protected $languageService;

    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    protected $locationService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    protected $contentTypeService;

    /** @var \Ibexa\Contracts\Core\Repository\SearchService */
    protected $searchService;

    /** @var \Ibexa\Contracts\Core\Repository\ObjectStateService */
    protected $objectStateService;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    protected $permissionResolver;

    /** @var \Ibexa\AdminUi\UI\Dataset\DatasetFactory */
    protected $datasetFactory;

    /** @var \Ibexa\AdminUi\UI\Service\PathService */
    protected $pathService;

    /** @var \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    private $userLanguagePreferenceProvider;

    /** @var \Ibexa\Core\Repository\LocationResolver\LocationResolver */
    protected $locationResolver;

    /**
     * @param \Ibexa\Contracts\Core\Repository\UserService $userService
     * @param \Ibexa\Contracts\Core\Repository\LanguageService $languageService
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     * @param \Ibexa\Contracts\Core\Repository\SearchService $searchService
     * @param \Ibexa\Contracts\Core\Repository\ObjectStateService $objectStateService
     * @param \Ibexa\Contracts\Core\Repository\PermissionResolver $permissionResolver
     * @param \Ibexa\AdminUi\UI\Service\PathService $pathService
     * @param \Ibexa\AdminUi\UI\Dataset\DatasetFactory $datasetFactory
     * @param \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider
     * @param \Ibexa\Core\Repository\LocationResolver\LocationResolver $locationResolver
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $versionInfo
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language $language
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $versionInfo
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function createLanguage(Language $language, VersionInfo $versionInfo): UIValue\Content\Language
    {
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Relation $relation
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Relation
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     */
    public function createRelation(Relation $relation, Content $content): UIValue\Content\Relation
    {
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\RelationListItem $relationListItem
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Relation
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     */
    public function createRelationItem(RelationListItem $relationListItem, Content $content): UIValue\Content\Relation
    {
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\UnauthorizedRelationListItem $relationListItem
     *
     * @return \Ibexa\AdminUi\UI\Value\Content\RelationInterface
     */
    public function createUnauthorizedRelationItem(
        UnauthorizedRelationListItem $relationListItem
    ): UIValue\Content\RelationInterface {
        return new UIValue\Content\UnauthorizedRelation($relationListItem);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     *
     * @return UIValue\ObjectState\ObjectState
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function createObjectState(
        ContentInfo $contentInfo,
        ObjectStateGroup $objectStateGroup
    ): UIValue\ObjectState\ObjectState {
        $objectState = $this->objectStateService->getContentState($contentInfo, $objectStateGroup);

        return new UIValue\ObjectState\ObjectState($objectState, [
            'userCanAssign' => $this->permissionResolver->canUser('state', 'assign', $contentInfo, [$objectState]),
        ]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\URLAlias $urlAlias
     *
     * @return \Ibexa\AdminUi\UI\Value\Content\UrlAlias
     */
    public function createUrlAlias(URLAlias $urlAlias): UIValue\Content\UrlAlias
    {
        return new UIValue\Content\UrlAlias($urlAlias);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment $roleAssignment
     *
     * @return \Ibexa\AdminUi\UI\Value\User\Role
     */
    public function createRole(RoleAssignment $roleAssignment): UIValue\User\Role
    {
        return new UIValue\User\Role($roleAssignment);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Policy $policy
     * @param \Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment $roleAssignment
     *
     * @return \Ibexa\AdminUi\UI\Value\User\Policy
     */
    public function createPolicy(Policy $policy, RoleAssignment $roleAssignment): UIValue\User\Policy
    {
        return new UIValue\User\Policy($policy, ['role_assignment' => $roleAssignment]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     *
     * @return \Ibexa\AdminUi\UI\Value\Location\Bookmark
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function createBookmark(Location $location): UIValue\Location\Bookmark
    {
        return new UIValue\Location\Bookmark(
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language $language
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     *
     * @return \Ibexa\AdminUi\UI\Value\Content\Language
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\DraftList\Item\ContentDraftListItem $contentDraftListItem
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     *
     * @return \Ibexa\AdminUi\UI\Value\Content\ContentDraftInterface
     */
    public function createContentDraft(
        ContentDraftListItem $contentDraftListItem,
        ContentType $contentType
    ): UIValue\Content\ContentDraftInterface {
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\DraftList\Item\UnauthorizedContentDraftListItem $contentDraftListItem
     *
     * @return \Ibexa\AdminUi\UI\Value\Content\ContentDraftInterface
     */
    public function createUnauthorizedContentDraft(
        UnauthorizedContentDraftListItem $contentDraftListItem
    ): UIValue\Content\ContentDraftInterface {
        return new UIValue\Content\UnauthorizedContentDraft($contentDraftListItem);
    }

    private function getRelationFieldDefinitionName(?Relation $relation, ContentType $contentType): string
    {
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
