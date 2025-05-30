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

    protected ContentService $contentService;

    private ContentTypeService $contentTypeService;

    protected LanguageService $languageService;

    protected ObjectStateService $objectStateService;

    protected ValueFactory $valueFactory;

    protected LocationService $locationService;

    private URLAliasService $urlAliasService;

    private RoleService $roleService;

    private UserService $userService;

    private BookmarkService $bookmarkService;

    private ConfigResolverInterface $configResolver;

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

    public function versions(): VersionsDataset
    {
        return new VersionsDataset($this->contentService, $this->valueFactory);
    }

    public function translations(): TranslationsDataset
    {
        return new TranslationsDataset($this->languageService, $this->valueFactory);
    }

    public function relationList(): RelationListDataset
    {
        return new RelationListDataset(
            $this->contentService,
            $this->valueFactory
        );
    }

    public function reverseRelationList(): ReverseRelationListDataset
    {
        return new ReverseRelationListDataset(
            $this->contentService,
            $this->valueFactory
        );
    }

    public function locations(): LocationsDataset
    {
        return new LocationsDataset($this->locationService, $this->valueFactory);
    }

    public function objectStates(): ObjectStatesDataset
    {
        return new ObjectStatesDataset($this->objectStateService, $this->valueFactory);
    }

    public function customUrls(): CustomUrlsDataset
    {
        return new CustomUrlsDataset($this->urlAliasService, $this->valueFactory, $this->logger);
    }

    public function roles(): RolesDataset
    {
        return new RolesDataset(
            $this->roleService,
            $this->userService,
            $this->valueFactory,
            $this->configResolver->getParameter('user_content_type_identifier'),
            $this->configResolver->getParameter('user_group_content_type_identifier')
        );
    }

    public function policies(): PoliciesDataset
    {
        return new PoliciesDataset(
            $this->roleService,
            $this->userService,
            $this->valueFactory,
            $this->configResolver->getParameter('user_content_type_identifier'),
            $this->configResolver->getParameter('user_group_content_type_identifier')
        );
    }

    public function bookmarks(): BookmarksDataset
    {
        return new BookmarksDataset(
            $this->bookmarkService,
            $this->valueFactory
        );
    }

    public function contentDraftList(): ContentDraftListDataset
    {
        return new ContentDraftListDataset(
            $this->contentService,
            $this->contentTypeService,
            $this->valueFactory
        );
    }
}
