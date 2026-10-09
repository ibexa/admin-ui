<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\UI\Dataset;

use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\BookmarkService;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DatasetFactory implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var ContentService */
    protected $contentService;

    /** @var ContentTypeService */
    private $contentTypeService;

    /** @var LanguageService */
    protected $languageService;

    /** @var ObjectStateService */
    protected $objectStateService;

    /** @var ValueFactory */
    protected $valueFactory;

    /** @var LocationService */
    protected $locationService;

    /** @var URLAliasService */
    private $urlAliasService;

    /** @var RoleService */
    private $roleService;

    /** @var UserService */
    private $userService;

    /** @var BookmarkService */
    private $bookmarkService;

    /** @var ConfigResolverInterface */
    private $configResolver;

    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        LanguageService $languageService,
        LocationService $locationService,
        ObjectStateService $objectStateService,
        URLAliasService $urlAliasService,
        RoleService $roleService,
        UserService $userService,
        BookmarkService $bookmarkService,
        ValueFactory $valueFactory,
        ConfigResolverInterface $configResolver,
        ?LoggerInterface $logger = null
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->languageService = $languageService;
        $this->locationService = $locationService;
        $this->objectStateService = $objectStateService;
        $this->urlAliasService = $urlAliasService;
        $this->roleService = $roleService;
        $this->userService = $userService;
        $this->bookmarkService = $bookmarkService;
        $this->valueFactory = $valueFactory;
        $this->configResolver = $configResolver;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @return VersionsDataset
     */
    public function versions(): VersionsDataset
    {
        return new VersionsDataset($this->contentService, $this->valueFactory);
    }

    /**
     * @return TranslationsDataset
     */
    public function translations(): TranslationsDataset
    {
        return new TranslationsDataset($this->languageService, $this->valueFactory);
    }

    /**
     * @deprecated since version 2.5, to be removed in 3.0. Please use DatasetFactory::relationList and DatasetFactory::reverseRelationList instead.
     *
     * @return RelationsDataset
     */
    public function relations(): RelationsDataset
    {
        return new RelationsDataset($this->contentService, $this->valueFactory);
    }

    /**
     * @return RelationListDataset
     */
    public function relationList(): RelationListDataset
    {
        return new RelationListDataset(
            $this->contentService,
            $this->valueFactory
        );
    }

    /**
     * @return ReverseRelationListDataset
     */
    public function reverseRelationList(): ReverseRelationListDataset
    {
        return new ReverseRelationListDataset(
            $this->contentService,
            $this->valueFactory
        );
    }

    /**
     * @return LocationsDataset
     */
    public function locations(): LocationsDataset
    {
        return new LocationsDataset($this->locationService, $this->valueFactory);
    }

    /**
     * @return ObjectStatesDataset
     */
    public function objectStates(): ObjectStatesDataset
    {
        return new ObjectStatesDataset($this->objectStateService, $this->valueFactory);
    }

    /**
     * @return CustomUrlsDataset
     */
    public function customUrls(): CustomUrlsDataset
    {
        return new CustomUrlsDataset($this->urlAliasService, $this->valueFactory, $this->logger);
    }

    /**
     * @return RolesDataset
     */
    public function roles(): RolesDataset
    {
        return new RolesDataset(
            $this->roleService,
            $this->contentService,
            $this->contentTypeService,
            $this->userService,
            $this->valueFactory,
            $this->configResolver->getParameter('user_content_type_identifier'),
            $this->configResolver->getParameter('user_group_content_type_identifier')
        );
    }

    /**
     * @return PoliciesDataset
     */
    public function policies(): PoliciesDataset
    {
        return new PoliciesDataset(
            $this->roleService,
            $this->contentService,
            $this->contentTypeService,
            $this->userService,
            $this->valueFactory,
            $this->configResolver->getParameter('user_content_type_identifier'),
            $this->configResolver->getParameter('user_group_content_type_identifier')
        );
    }

    /**
     * @return BookmarksDataset
     */
    public function bookmarks(): BookmarksDataset
    {
        return new BookmarksDataset(
            $this->bookmarkService,
            $this->valueFactory
        );
    }

    /**
     * @deprecated since version 2.5, to be removed in 3.0. Please use DatasetFactory::contentDraftList instead.
     *
     * @return ContentDraftsDataset
     */
    public function contentDrafts(): ContentDraftsDataset
    {
        return new ContentDraftsDataset(
            $this->contentService,
            $this->contentTypeService,
            $this->locationService
        );
    }

    /**
     * @return ContentDraftListDataset
     */
    public function contentDraftList(): ContentDraftListDataset
    {
        return new ContentDraftListDataset(
            $this->contentService,
            $this->contentTypeService,
            $this->valueFactory
        );
    }
}

class_alias(DatasetFactory::class, 'EzSystems\EzPlatformAdminUi\UI\Dataset\DatasetFactory');
