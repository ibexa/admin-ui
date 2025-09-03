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
use RuntimeException;

class ValueFactory
{
    public function __construct(
        protected UserService $userService,
        protected LanguageService $languageService,
        protected LocationService $locationService,
        protected ContentTypeService $contentTypeService,
        protected SearchService $searchService,
        protected ObjectStateService $objectStateService,
        protected PermissionResolver $permissionResolver,
        protected PathService $pathService,
        protected DatasetFactory $datasetFactory,
        private UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        protected LocationResolver $locationResolver
    ) {
    }

    /**
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
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function createLanguage(Language $language, VersionInfo $versionInfo): UIValue\Content\Language
    {
        $target = (new VersionBuilder())->translateToAnyLanguageOf([
            $language->getLanguageCode(),
        ])->build();

        return new UIValue\Content\Language($language, [
            'userCanRemove' => $this->permissionResolver->canUser('content', 'remove', $versionInfo, [$target]),
            'userCanEdit' => $this->permissionResolver->canUser('content', 'edit', $versionInfo),
            'main' => $language->languageCode === $versionInfo->getContentInfo()->getMainLanguageCode(),
        ]);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     */
    public function createRelationItem(RelationListItem $relationListItem, Content $content): UIValue\Content\Relation
    {
        $contentType = $content->getContentType();
        $relation = $relationListItem->getRelation();
        if ($relation === null) {
            throw new RuntimeException('RelationListItem does not have a relation.');
        }

        return new UIValue\Content\Relation($relation, [
            'relationFieldDefinitionName' => $this->getRelationFieldDefinitionName($relation, $contentType),
            'relationContentTypeName' => $contentType->getName(),
            'relationLocation' => $this->locationResolver->resolveLocation($content->getContentInfo()),
            'relationName' => $content->getName(),
            'resolvedSourceLocation' => $this->locationResolver->resolveLocation($relation->getSourceContentInfo()),
            'resolvedDestinationLocation' => $this->locationResolver->resolveLocation(
                $relation->getDestinationContentInfo()
            ),
        ]);
    }

    public function createUnauthorizedRelationItem(
        UnauthorizedRelationListItem $relationListItem
    ): UIValue\Content\RelationInterface {
        return new UIValue\Content\UnauthorizedRelation($relationListItem);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function createLocation(Location $location): UIValue\Content\Location
    {
        $translations = $location->getContent()->getVersionInfo()->getLanguageCodes();
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
            'main' => $location->getContentInfo()->getMainLocationId() === $location->getId(),
        ]);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function createObjectState(
        ContentInfo $contentInfo,
        ObjectStateGroup $objectStateGroup
    ): ObjectState\ObjectState {
        $objectState = $this->objectStateService->getContentState($contentInfo, $objectStateGroup);

        return new ObjectState\ObjectState($objectState, [
            'userCanAssign' => $this->permissionResolver->canUser(
                'state',
                'assign',
                $contentInfo,
                [$objectState]
            ),
        ]);
    }

    public function createUrlAlias(URLAlias $urlAlias): UIValue\Content\UrlAlias
    {
        return new UIValue\Content\UrlAlias($urlAlias);
    }

    public function createRole(RoleAssignment $roleAssignment): User\Role
    {
        return new User\Role($roleAssignment);
    }

    public function createPolicy(Policy $policy, RoleAssignment $roleAssignment): User\Policy
    {
        return new User\Policy($policy, ['roleAssignment' => $roleAssignment]);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
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
                'userCanEdit' => $this->permissionResolver->canUser(
                    'content',
                    'edit',
                    $location->getContentInfo()
                ),
            ]
        );
    }

    /**
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

    public function createContentDraft(
        ContentDraftListItem $contentDraftListItem,
        ContentType $contentType
    ): UIValue\Content\ContentDraftInterface {
        $versionInfo = $contentDraftListItem->getVersionInfo();
        if ($versionInfo === null) {
            throw new RuntimeException('ContentDraftListItem does not have associated VersionInfo.');
        }

        $contentInfo = $versionInfo->getContentInfo();

        $versionId = new UIValue\Content\VersionId(
            $contentInfo->getId(),
            $versionInfo->getVersionNo()
        );

        return new UIValue\Content\ContentDraft(
            $versionInfo,
            $versionId,
            $contentType
        );
    }

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
                return $fieldDefinition->getName() ?? '';
            }
        }

        return '';
    }
}
