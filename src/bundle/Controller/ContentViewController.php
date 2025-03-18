<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\Content\ContentVisibilityUpdateData;
use Ibexa\AdminUi\Form\Data\Content\Draft\ContentCreateData;
use Ibexa\AdminUi\Form\Data\Content\Draft\ContentEditData;
use Ibexa\AdminUi\Form\Data\Location\LocationCopyData;
use Ibexa\AdminUi\Form\Data\Location\LocationCopySubtreeData;
use Ibexa\AdminUi\Form\Data\Location\LocationMoveData;
use Ibexa\AdminUi\Form\Data\Location\LocationTrashData;
use Ibexa\AdminUi\Form\Data\User\UserDeleteData;
use Ibexa\AdminUi\Form\Data\User\UserEditData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\Type\ChoiceList\Loader\ContentEditTranslationChoiceLoader;
use Ibexa\AdminUi\Form\Type\Content\ContentVisibilityUpdateType;
use Ibexa\AdminUi\Form\Type\User\UserInvitationType;
use Ibexa\AdminUi\Permission\LookupLimitationsTransformer;
use Ibexa\AdminUi\Specification\ContentIsUser;
use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUserGroup;
use Ibexa\AdminUi\UI\Module\Subitems\ContentViewParameterSupplier;
use Ibexa\AdminUi\UI\Module\Subitems\ContentViewParameterSupplier as SubitemsContentViewParameterSupplier;
use Ibexa\AdminUi\UI\Service\PathService;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\Core\Repository\BookmarkService;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Ibexa\Core\MVC\Symfony\View\ContentView;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ContentViewController extends Controller
{
    private ContentTypeService $contentTypeService;

    private LanguageService $languageService;

    private PathService $pathService;

    private FormFactory $formFactory;

    private ContentViewParameterSupplier $subitemsContentViewParameterSupplier;

    private UserService $userService;

    private BookmarkService $bookmarkService;

    private ContentService $contentService;

    private LocationService $locationService;

    private UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider;

    private FormFactoryInterface $sfFormFactory;

    private ConfigResolverInterface $configResolver;

    private Repository $repository;

    private PermissionResolver $permissionResolver;

    private LookupLimitationsTransformer $lookupLimitationsTransformer;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     * @param \Ibexa\Contracts\Core\Repository\LanguageService $languageService
     * @param \Ibexa\AdminUi\UI\Service\PathService $pathService
     * @param \Ibexa\AdminUi\Form\Factory\FormFactory $formFactory
     * @param \Symfony\Component\Form\FormFactoryInterface $sfFormFactory
     * @param \Ibexa\AdminUi\UI\Module\Subitems\ContentViewParameterSupplier $subitemsContentViewParameterSupplier
     * @param \Ibexa\Contracts\Core\Repository\UserService $userService
     * @param \Ibexa\Contracts\Core\Repository\BookmarkService $bookmarkService
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     * @param \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider
     * @param \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface $configResolver
     * @param \Ibexa\Contracts\Core\Repository\Repository $repository
     * @param \Ibexa\Contracts\Core\Repository\PermissionResolver $permissionResolver
     * @param \Ibexa\AdminUi\Permission\LookupLimitationsTransformer $lookupLimitationsTransformer
     */
    public function __construct(
        ContentTypeService $contentTypeService,
        LanguageService $languageService,
        PathService $pathService,
        FormFactory $formFactory,
        FormFactoryInterface $sfFormFactory,
        SubitemsContentViewParameterSupplier $subitemsContentViewParameterSupplier,
        UserService $userService,
        BookmarkService $bookmarkService,
        ContentService $contentService,
        LocationService $locationService,
        UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        ConfigResolverInterface $configResolver,
        Repository $repository,
        PermissionResolver $permissionResolver,
        LookupLimitationsTransformer $lookupLimitationsTransformer
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->languageService = $languageService;
        $this->pathService = $pathService;
        $this->formFactory = $formFactory;
        $this->sfFormFactory = $sfFormFactory;
        $this->subitemsContentViewParameterSupplier = $subitemsContentViewParameterSupplier;
        $this->userService = $userService;
        $this->bookmarkService = $bookmarkService;
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->userLanguagePreferenceProvider = $userLanguagePreferenceProvider;
        $this->configResolver = $configResolver;
        $this->permissionResolver = $permissionResolver;
        $this->lookupLimitationsTransformer = $lookupLimitationsTransformer;
        $this->repository = $repository;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     *
     * @return \Ibexa\Core\MVC\Symfony\View\ContentView
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function locationViewAction(Request $request, ContentView $view): ContentView
    {
        // We should not cache ContentView because we use forms with CSRF tokens in template
        // JIRA ref: https://issues.ibexa.co/browse/EZP-28190
        $view->setCacheEnabled(false);

        if (!$view->getContent()->contentInfo->isTrashed()) {
            $this->supplyPathLocations($view);
            $this->subitemsContentViewParameterSupplier->supply($view);
            $this->supplyContentActionForms($view);
            $this->supplyContentReverseRelations($view);
            $this->supplyContentTreeParameters($view);
        }

        $this->supplyContentType($view);
        $this->supplyDraftPagination($view, $request);
        $this->supplyRelationPagination($view, $request);
        $this->supplyReverseRelationPagination($view, $request);
        $this->supplyCustomUrlPagination($view, $request);
        $this->supplySystemUrlPagination($view, $request);
        $this->supplyRolePagination($view, $request);
        $this->supplyPolicyPagination($view, $request);
        $this->supplyIsLocationBookmarked($view);
        $this->supplyUserInvitation($view);

        return $view;
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     *
     * @return \Ibexa\Core\MVC\Symfony\View\ContentView
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function embedViewAction(ContentView $view): ContentView
    {
        // We should not cache ContentView because we use forms with CSRF tokens in template
        // JIRA ref: https://issues.ibexa.co/browse/EZP-28190
        $view->setCacheEnabled(false);

        $this->supplyPathLocations($view);
        $this->supplyContentType($view);

        return $view;
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     */
    private function supplyPathLocations(ContentView $view): void
    {
        $location = $view->getLocation();
        $pathLocations = $this->pathService->loadPathLocations($location);
        $view->addParameters(['path_locations' => $pathLocations]);
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function supplyContentType(ContentView $view): void
    {
        $contentType = $this->contentTypeService->loadContentType(
            $view->getContent()->contentInfo->contentTypeId,
            $this->userLanguagePreferenceProvider->getPreferredLanguages()
        );
        $view->addParameters(['content_type' => $contentType]);
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function supplyContentActionForms(ContentView $view): void
    {
        $location = $view->getLocation();
        $content = $view->getContent();
        $versionInfo = $content->getVersionInfo();

        $locationCopyType = $this->formFactory->copyLocation(
            new LocationCopyData($location)
        );

        $locationMoveType = $this->formFactory->moveLocation(
            new LocationMoveData($location)
        );

        $subitemsContentEdit = $this->formFactory->contentEdit(
            null,
            'form_subitems_content_edit'
        );

        $contentCreateType = $this->formFactory->createContent(
            $this->getContentCreateData($location)
        );

        $locationCopySubtreeType = $this->formFactory->copyLocationSubtree(
            new LocationCopySubtreeData($location)
        );

        $contentVisibilityUpdateForm = $this->sfFormFactory->create(
            ContentVisibilityUpdateType::class,
            new ContentVisibilityUpdateData(
                $location->getContentInfo(),
                $location,
                $location->getContentInfo()->isHidden
            )
        );

        $locationTrashType = $this->formFactory->trashLocation(
            new LocationTrashData($location)
        );

        $contentEditType = $this->createContentEditForm(
            $content->contentInfo,
            $versionInfo,
            null,
            $location
        );

        $view->addParameters([
            'form_location_copy' => $locationCopyType->createView(),
            'form_location_move' => $locationMoveType->createView(),
            'form_content_create' => $contentCreateType->createView(),
            'form_content_visibility_update' => $contentVisibilityUpdateForm->createView(),
            'form_subitems_content_edit' => $subitemsContentEdit->createView(),
            'form_location_copy_subtree' => $locationCopySubtreeType->createView(),
            'form_location_trash' => $locationTrashType->createView(),
            'form_content_edit' => $contentEditType->createView(),
        ]);

        if ((new ContentIsUser($this->userService))->isSatisfiedBy($content)) {
            $userDeleteType = $this->formFactory->deleteUser(
                new UserDeleteData($content->contentInfo)
            );
            $userEditType = $this->formFactory->editUser(
                new UserEditData($content->contentInfo, $versionInfo, null, $location)
            );

            $view->addParameters([
                'form_user_delete' => $userDeleteType->createView(),
                'form_user_edit' => $userEditType->createView(),
            ]);
        }
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null $contentInfo
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo|null $versionInfo
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language|null $language
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createContentEditForm(
        ?ContentInfo $contentInfo = null,
        ?VersionInfo $versionInfo = null,
        ?Language $language = null,
        ?Location $location = null
    ): FormInterface {
        $languageCodes = $versionInfo->languageCodes ?? [];

        return $this->formFactory->contentEdit(
            new ContentEditData($contentInfo, null, $language, $location),
            null,
            [
                'choice_loader' => new ContentEditTranslationChoiceLoader(
                    $this->languageService,
                    $this->permissionResolver,
                    $contentInfo,
                    $this->lookupLimitationsTransformer,
                    $languageCodes,
                    $this->locationService,
                    $location
                ),
            ]
        );
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    private function supplyDraftPagination(ContentView $view, Request $request): void
    {
        $page = $request->query->all('page');

        $view->addParameters([
            'draft_pagination_params' => [
                'route_name' => $request->get('_route'),
                'route_params' => $request->get('_route_params'),
                'page' => (int) ($page['version_draft'] ?? 1),
                'pages_map' => $page,
                'limit' => $this->configResolver->getParameter('pagination.version_draft_limit'),
            ],
        ]);
    }

    private function supplyRelationPagination(ContentView $view, Request $request): void
    {
        $page = $request->query->all('page');

        $view->addParameters([
            'relation_pagination_params' => [
                'route_name' => $request->get('_route'),
                'route_params' => $request->get('_route_params'),
                'page' => (int) ($page['relation'] ?? 1),
                'pages_map' => $page,
                'limit' => $this->configResolver->getParameter('pagination.relation_limit'),
            ],
        ]);
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    private function supplyReverseRelationPagination(ContentView $view, Request $request): void
    {
        $page = $request->query->all('page');

        $view->addParameters([
            'reverse_relation_pagination_params' => [
                'route_name' => $request->get('_route'),
                'route_params' => $request->get('_route_params'),
                'page' => (int) ($page['reverse_relation'] ?? 1),
                'pages_map' => $page,
                'limit' => $this->configResolver->getParameter('pagination.reverse_relation_limit'),
            ],
        ]);
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    private function supplyCustomUrlPagination(ContentView $view, Request $request): void
    {
        $page = $request->query->all('page');

        $view->addParameters([
            'custom_urls_pagination_params' => [
                'route_name' => $request->get('_route'),
                'route_params' => $request->get('_route_params'),
                'page' => (int) ($page['custom_url'] ?? 1),
                'limit' => $this->configResolver->getParameter('pagination.content_custom_url_limit'),
            ],
        ]);
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    private function supplySystemUrlPagination(ContentView $view, Request $request): void
    {
        $page = $request->query->all('page');

        $view->addParameters([
            'system_urls_pagination_params' => [
                'route_name' => $request->get('_route'),
                'route_params' => $request->get('_route_params'),
                'page' => (int) ($page['system_url'] ?? 1),
                'limit' => $this->configResolver->getParameter('pagination.content_system_url_limit'),
            ],
        ]);
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    private function supplyRolePagination(ContentView $view, Request $request): void
    {
        $page = $request->query->all('page');

        $view->addParameters([
            'roles_pagination_params' => [
                'route_name' => $request->get('_route'),
                'route_params' => $request->get('_route_params'),
                'page' => (int) ($page['role'] ?? 1),
                'limit' => $this->configResolver->getParameter('pagination.content_role_limit'),
            ],
        ]);
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    private function supplyPolicyPagination(ContentView $view, Request $request): void
    {
        $page = $request->query->all('page');

        $view->addParameters([
            'policies_pagination_params' => [
                'route_name' => $request->get('_route'),
                'route_params' => $request->get('_route_params'),
                'page' => (int) ($page['policy'] ?? 1),
                'limit' => $this->configResolver->getParameter('pagination.content_policy_limit'),
            ],
        ]);
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     */
    private function supplyContentTreeParameters(ContentView $view): void
    {
        $view->addParameters([
            'content_tree_module_root' => $this->resolveTreeRootLocationId($view->getLocation()),
        ]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     *
     * @return int
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function resolveTreeRootLocationId(?Location $location): int
    {
        if (null === $location) {
            return $this->configResolver->getParameter('content_tree_module.tree_root_location_id');
        }

        $contextualContentTreeRootLocationIds = $this->configResolver->getParameter('content_tree_module.contextual_tree_root_location_ids');
        $possibleContentTreeRoots = array_intersect($location->path, $contextualContentTreeRootLocationIds);
        if (\is_array($this->permissionResolver->hasAccess('content', 'read'))) {
            $accessibleLocations = $this->locationService->loadLocationList($possibleContentTreeRoots);
            $possibleContentTreeRoots = array_column($accessibleLocations, 'id');
        }

        if (empty($possibleContentTreeRoots)) {
            // if a user has no access to any tree root than current location id is set
            return $location->id;
        }

        // use the outermost ancestor
        return (int)reset($possibleContentTreeRoots);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     *
     * @return \Ibexa\AdminUi\Form\Data\Content\Draft\ContentCreateData
     */
    private function getContentCreateData(?Location $location): ContentCreateData
    {
        $languages = $this->languageService->loadLanguages();
        $language = 1 === \count($languages)
            ? array_shift($languages)
            : null;

        return new ContentCreateData(null, $location, $language);
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     */
    private function supplyIsLocationBookmarked(ContentView $view): void
    {
        $locationIsBookmarked = $view->getLocation() ? $this->bookmarkService->isBookmarked($view->getLocation()) : false;

        $view->addParameters(['location_is_bookmarked' => $locationIsBookmarked]);
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\ContentView $view
     */
    private function supplyContentReverseRelations(ContentView $view): void
    {
        $contentInfo = $view->getLocation()->getContentInfo();

        $hasReverseRelations = $this->permissionResolver->sudo(
            static function (Repository $repository) use ($contentInfo): bool {
                return $repository->getContentService()->countReverseRelations($contentInfo) > 0;
            },
            $this->repository
        );

        $view->addParameters(['content_has_reverse_relations' => $hasReverseRelations]);
    }

    private function supplyUserInvitation(ContentView $view): void
    {
        $content = $view->getContent();
        $contentType = $this->contentTypeService->loadContentType(
            $view->getContent()->getVersionInfo()->getContentInfo()->contentTypeId,
            $this->userLanguagePreferenceProvider->getPreferredLanguages()
        );
        $userGroupContentTypeIdentifier = $this->configResolver->getParameter('user_group_content_type_identifier');
        $contentIsUserGroup = (new ContentTypeIsUserGroup($userGroupContentTypeIdentifier))
            ->isSatisfiedBy($contentType);

        $canSendInvitation = $this->permissionResolver->canUser(
            'user',
            'invite',
            $content
        );

        if ($contentIsUserGroup && $canSendInvitation) {
            $userInvitationForm = $this->getInvitationForm($content);

            $view->addParameters([
                'form_user_invitation' => $userInvitationForm->createView(),
            ]);
        }
    }

    private function getInvitationForm(Content $content): FormInterface
    {
        return $this->sfFormFactory->create(
            UserInvitationType::class,
            null,
            [
                'action' => $this->generateUrl(
                    'ibexa.user.invite.to_group',
                    [
                        'userGroupId' => $content->getVersionInfo()->getContentInfo()->id,
                    ]
                ),
                'method' => Request::METHOD_POST,
            ]
        );
    }
}
