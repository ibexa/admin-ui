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

class DatasetFactory
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    protected $contentService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Ibexa\Contracts\Core\Repository\LanguageService */
    protected $languageService;

    /** @var \Ibexa\Contracts\Core\Repository\ObjectStateService */
    protected $objectStateService;

    /** @var \Ibexa\AdminUi\UI\Value\ValueFactory */
    protected $valueFactory;

    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    protected $locationService;

    /** @var \Ibexa\Contracts\Core\Repository\URLAliasService */
    private $urlAliasService;

    /** @var \Ibexa\Contracts\Core\Repository\RoleService */
    private $roleService;

    /** @var \Ibexa\Contracts\Core\Repository\UserService */
    private $userService;

    /** @var \Ibexa\Contracts\Core\Repository\BookmarkService */
    private $bookmarkService;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
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
        ConfigResolverInterface $configResolver
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
    }

    /**
     * @return \Ibexa\AdminUi\UI\Dataset\VersionsDataset
     */
    public function versions(): VersionsDataset
    {
        return new VersionsDataset($this->contentService, $this->valueFactory);
    }

    /**
     * @return \Ibexa\AdminUi\UI\Dataset\TranslationsDataset
     */
    public function translations(): TranslationsDataset
    {
        return new TranslationsDataset($this->languageService, $this->valueFactory);
    }

    /**
     * @deprecated since version 2.5, to be removed in 3.0. Please use DatasetFactory::relationList and DatasetFactory::reverseRelationList instead.
     *
     * @return \Ibexa\AdminUi\UI\Dataset\RelationsDataset
     */
    public function relations(): RelationsDataset
    {
        return new RelationsDataset($this->contentService, $this->valueFactory);
    }

    /**
     * @return \Ibexa\AdminUi\UI\Dataset\RelationListDataset
     */
    public function relationList(): RelationListDataset
    {
        return new RelationListDataset(
            $this->contentService,
            $this->valueFactory
        );
    }

    /**
     * @return \Ibexa\AdminUi\UI\Dataset\ReverseRelationListDataset
     */
    public function reverseRelationList(): ReverseRelationListDataset
    {
        return new ReverseRelationListDataset(
            $this->contentService,
            $this->valueFactory
        );
    }

    /**
     * @return \Ibexa\AdminUi\UI\Dataset\LocationsDataset
     */
    public function locations(): LocationsDataset
    {
        return new LocationsDataset($this->locationService, $this->valueFactory);
    }

    /**
     * @return \Ibexa\AdminUi\UI\Dataset\ObjectStatesDataset
     */
    public function objectStates(): ObjectStatesDataset
    {
        return new ObjectStatesDataset($this->objectStateService, $this->valueFactory);
    }

    /**
     * @return \Ibexa\AdminUi\UI\Dataset\CustomUrlsDataset
     */
    public function customUrls(): CustomUrlsDataset
    {
        return new CustomUrlsDataset($this->urlAliasService, $this->valueFactory);
    }

    /**
     * @return \Ibexa\AdminUi\UI\Dataset\RolesDataset
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
     * @return \Ibexa\AdminUi\UI\Dataset\PoliciesDataset
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
     * @return \Ibexa\AdminUi\UI\Dataset\BookmarksDataset
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
     * @return \Ibexa\AdminUi\UI\Dataset\ContentDraftsDataset
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
     * @return \Ibexa\AdminUi\UI\Dataset\ContentDraftListDataset
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
