<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Event\Options;
use Ibexa\AdminUi\Form\Data\Content\ContentVisibilityUpdateData;
use Ibexa\AdminUi\Form\Data\Content\Draft\ContentCreateData;
use Ibexa\AdminUi\Form\Data\Content\Draft\ContentEditData;
use Ibexa\AdminUi\Form\Data\Content\Location\ContentMainLocationUpdateData;
use Ibexa\AdminUi\Form\Data\Content\Translation\MainTranslationUpdateData;
use Ibexa\AdminUi\Form\DataMapper\ContentMainLocationUpdateMapper;
use Ibexa\AdminUi\Form\DataMapper\MainTranslationUpdateMapper;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Form\Type\Content\Translation\MainTranslationUpdateType;
use Ibexa\AdminUi\Form\Type\Preview\SiteAccessChoiceType;
use Ibexa\AdminUi\Permission\LookupLimitationsTransformer;
use Ibexa\AdminUi\Siteaccess\SiteAccessNameGeneratorInterface;
use Ibexa\AdminUi\Siteaccess\SiteaccessResolverInterface;
use Ibexa\AdminUi\Specification\ContentIsUser;
use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Event\ContentProxyCreateEvent;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Limitation\Target;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions as ApiException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Base\Exceptions\BadStateException;
use Ibexa\Core\Helper\TranslationHelper;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ContentController extends Controller
{
    /** @var \Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface */
    private $notificationHandler;

    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /** @var \Ibexa\AdminUi\Form\Factory\FormFactory */
    private $formFactory;

    /** @var \Ibexa\AdminUi\Form\SubmitHandler */
    private $submitHandler;

    /** @var \Ibexa\AdminUi\Form\DataMapper\ContentMainLocationUpdateMapper */
    private $contentMainLocationUpdateMapper;

    /** @var \Ibexa\AdminUi\Siteaccess\SiteaccessResolverInterface */
    private $siteaccessResolver;

    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $locationService;

    /** @var \Ibexa\Contracts\Core\Repository\UserService */
    private $userService;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \Ibexa\AdminUi\Permission\LookupLimitationsTransformer */
    private $lookupLimitationsTransformer;

    /** @var \Ibexa\Core\Helper\TranslationHelper */
    private $translationHelper;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    /** @var \Ibexa\AdminUi\Siteaccess\SiteAccessNameGeneratorInterface */
    private $siteAccessNameGenerator;

    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcher;

    private FormFactoryInterface $baseFormFactory;

    public function __construct(
        TranslatableNotificationHandlerInterface $notificationHandler,
        ContentService $contentService,
        FormFactory $formFactory,
        SubmitHandler $submitHandler,
        ContentMainLocationUpdateMapper $contentMetadataUpdateMapper,
        SiteaccessResolverInterface $siteaccessResolver,
        LocationService $locationService,
        UserService $userService,
        PermissionResolver $permissionResolver,
        LookupLimitationsTransformer $lookupLimitationsTransformer,
        TranslationHelper $translationHelper,
        ConfigResolverInterface $configResolver,
        SiteAccessNameGeneratorInterface $siteAccessNameGenerator,
        EventDispatcherInterface $eventDispatcher,
        FormFactoryInterface $baseFormFactory
    ) {
        $this->notificationHandler = $notificationHandler;
        $this->contentService = $contentService;
        $this->formFactory = $formFactory;
        $this->submitHandler = $submitHandler;
        $this->contentMainLocationUpdateMapper = $contentMetadataUpdateMapper;
        $this->siteaccessResolver = $siteaccessResolver;
        $this->locationService = $locationService;
        $this->userService = $userService;
        $this->permissionResolver = $permissionResolver;
        $this->translationHelper = $translationHelper;
        $this->lookupLimitationsTransformer = $lookupLimitationsTransformer;
        $this->configResolver = $configResolver;
        $this->siteAccessNameGenerator = $siteAccessNameGenerator;
        $this->eventDispatcher = $eventDispatcher;
        $this->baseFormFactory = $baseFormFactory;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws ApiException\ContentValidationException
     * @throws ApiException\ContentFieldValidationException
     */
    public function createAction(Request $request): Response
    {
        $formName = $request->query->get('formName');
        $form = $this->formFactory->createContent(null, $formName);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ContentCreateData $data) {
                $contentType = $data->getContentType();
                $language = $data->getLanguage();
                $parentLocation = $data->getParentLocation();

                if ((new ContentTypeIsUser($this->configResolver->getParameter('user_content_type_identifier')))->isSatisfiedBy($contentType)) {
                    return $this->redirectToRoute('ibexa.user.create', [
                        'contentTypeIdentifier' => $contentType->identifier,
                        'language' => $language->languageCode,
                        'parentLocationId' => $parentLocation->id,
                    ]);
                }

                return $this->redirectToRoute('ibexa.content.create.proxy', [
                    'contentTypeIdentifier' => $contentType->identifier,
                    'languageCode' => $language->languageCode,
                    'parentLocationId' => $parentLocation->id,
                ]);
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirect($this->generateUrl('ibexa.dashboard'));
    }

    public function proxyCreateAction(
        ContentType $contentType,
        string $languageCode,
        int $parentLocationId
    ): Response {
        /** @var \Ibexa\Contracts\AdminUi\Event\ContentProxyCreateEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new ContentProxyCreateEvent(
                $contentType,
                $languageCode,
                $parentLocationId,
                new Options()
            )
        );

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        // Fallback to "nodraft"
        return $this->redirectToRoute('ibexa.content.create_no_draft', [
            'contentTypeIdentifier' => $contentType->identifier,
            'language' => $languageCode,
            'parentLocationId' => $parentLocationId,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function editAction(Request $request): Response
    {
        /* @todo it shouldn't rely on keys from request */
        $requestKeys = $request->request->keys();
        $formName = $request->query->get(
            'formName',
            reset($requestKeys) ?: null
        );

        $form = $this->formFactory->contentEdit(null, $formName);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ContentEditData $data) {
                $contentInfo = $data->getContentInfo();
                $language = $data->getLanguage();
                $location = $data->getLocation();

                $content = $this->contentService->loadContent($contentInfo->id);
                $versionInfo = $data->getVersionInfo() ?? $content->getVersionInfo();
                $versionNo = $versionInfo->versionNo;

                if ((new ContentIsUser($this->userService))->isSatisfiedBy($content)) {
                    return $this->redirectToRoute('ibexa.user.update', [
                        'contentId' => $contentInfo->id,
                        'versionNo' => $versionNo,
                        'language' => $language->languageCode,
                    ]);
                }

                if (!$versionInfo->isDraft()) {
                    $contentDraft = $this->contentService->createContentDraft($contentInfo, $versionInfo, null, $language);
                    $versionNo = $contentDraft->getVersionInfo()->versionNo;

                    $this->notificationHandler->success(
                        /** @Desc("Created a new draft for '%name%'.") */
                        'content.create_draft.success',
                        ['%name%' => $this->translationHelper->getTranslatedContentName($content)],
                        'ibexa_content'
                    );
                }

                return $this->redirectToRoute('ibexa.content.draft.edit', [
                    'contentId' => $contentInfo->id,
                    'versionNo' => $versionNo,
                    'language' => $language->languageCode,
                    'locationId' => null !== $location
                        ? $location->id
                        : $contentInfo->mainLocationId,
                ]);
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        /** @var \Ibexa\AdminUi\Form\Data\Content\Draft\ContentEditData $data */
        $data = $form->getData();
        $contentInfo = $data->getContentInfo();

        if (null !== $contentInfo) {
            return $this->redirectToRoute('ibexa.content.view', [
                'contentId' => $contentInfo->id,
                'locationId' => $contentInfo->mainLocationId,
            ]);
        }

        return $this->redirectToRoute('ibexa.dashboard');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function updateMainLocationAction(Request $request): Response
    {
        $form = $this->formFactory->updateContentMainLocation();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ContentMainLocationUpdateData $data) {
                $contentInfo = $data->getContentInfo();

                $contentMetadataUpdateStruct = $this->contentMainLocationUpdateMapper->reverseMap($data);

                $this->contentService->updateContentMetadata($contentInfo, $contentMetadataUpdateStruct);

                $this->notificationHandler->success(
                    /** @Desc("Main Location for '%name%' updated.") */
                    'content.main_location_update.success',
                    ['%name%' => $contentInfo->name],
                    'ibexa_content'
                );

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $contentInfo->id,
                    'locationId' => $contentInfo->mainLocationId,
                    '_fragment' => 'ibexa-tab-location-view-locations',
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        /** @var \Ibexa\AdminUi\Form\Data\Content\Draft\ContentEditData $data */
        $data = $form->getData();
        $contentInfo = $data->getContentInfo();

        if (null !== $contentInfo) {
            return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                'contentId' => $contentInfo->id,
                'locationId' => $contentInfo->mainLocationId,
                '_fragment' => 'ibexa-tab-location-view-locations',
            ]));
        }

        return $this->redirectToRoute('ibexa.dashboard');
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     * @param string|null $languageCode
     * @param int|null $versionNo
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function previewAction(
        Request $request,
        Content $content,
        ?string $languageCode = null,
        ?int $versionNo = null,
        ?Location $location = null
    ): Response {
        $preselectedSiteAccess = $request->query->get('preselectedSiteAccess');
        $referrer = $request->query->get('referrer');

        if (null === $languageCode) {
            $languageCode = $content->contentInfo->mainLanguageCode;
        }

        // nonpublished content should use parent location instead because location doesn't exist yet
        if (!$content->contentInfo->published && null === $content->contentInfo->mainLocationId) {
            $versionInfo = $this->contentService->loadVersionInfo($content->contentInfo, $versionNo);
            $parentLocations = $this->locationService->loadParentLocationsForDraftContent($versionInfo);
            $location = reset($parentLocations);
            $versionNo = null;
        }

        if (null === $location) {
            $location = $this->locationService->loadLocation($content->contentInfo->mainLocationId);
        }

        $siteAccesses = $this->siteaccessResolver->getSiteAccessesListForLocation($location, $versionNo, $languageCode);

        if (empty($siteAccesses)) {
            throw new BadStateException(
                'siteaccess',
                'There is no SiteAccess available for this Content item'
            );
        }

        $siteAccessesList = [];
        foreach ($siteAccesses as $siteAccess) {
            $siteAccessesList[$siteAccess->name] = $this->siteAccessNameGenerator->generate($siteAccess);
        }

        if (
            $preselectedSiteAccess !== null &&
            !array_key_exists($preselectedSiteAccess, $siteAccessesList)
        ) {
            $preselectedSiteAccess = null;
        }

        $siteAccessSelector = $this->baseFormFactory->create(
            SiteAccessChoiceType::class,
            $preselectedSiteAccess,
            [
                'location' => $location,
                'content' => $content,
                'versionNo' => $versionNo ?? $content->getVersionInfo()->versionNo,
                'languageCode' => $languageCode,
            ]
        );

        return $this->render('@ibexadesign/content/content_preview.html.twig', [
            'location' => $location,
            'content' => $content,
            'language_code' => $languageCode,
            'siteaccesses' => $siteAccessesList,
            'site_access_form' => $siteAccessSelector->createView(),
            'version_no' => $versionNo ?? $content->getVersionInfo()->versionNo,
            'preselected_site_access' => $preselectedSiteAccess,
            'referrer' => $referrer ?? 'content_draft_edit',
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateMainTranslationAction(Request $request): Response
    {
        $form = $this->createForm(MainTranslationUpdateType::class, new MainTranslationUpdateData());
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (MainTranslationUpdateData $data) {
                $content = $data->getContent();
                $contentInfo = $content->contentInfo;
                $mapper = new MainTranslationUpdateMapper();
                $contentMetadataUpdateStruct = $mapper->reverseMap($data);
                $this->contentService->updateContentMetadata($contentInfo, $contentMetadataUpdateStruct);
                $this->notificationHandler->success(
                    /** @Desc("Main language for '%name%' updated.") */
                    'content.main_language_update.success',
                    ['%name%' => $this->translationHelper->getTranslatedContentName($content)],
                    'ibexa_content'
                );

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $contentInfo->id,
                    'locationId' => $contentInfo->mainLocationId,
                    '_fragment' => 'ibexa-tab-location-view-translations',
                ]));
            });
            if ($result instanceof Response) {
                return $result;
            }
        }
        /** @var \Ibexa\AdminUi\Form\Data\Content\Translation\MainTranslationUpdateData $data */
        $data = $form->getData();
        $contentInfo = $data->getContentInfo();
        if (null !== $contentInfo) {
            return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                'contentId' => $contentInfo->id,
                'locationId' => $contentInfo->mainLocationId,
                '_fragment' => 'ibexa-tab-location-view-translations',
            ]));
        }

        return $this->redirectToRoute('ibexa.dashboard');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateVisibilityAction(Request $request): Response
    {
        $formName = $request->query->get('formName');
        $form = $this->formFactory->updateVisibilityContent(null, $formName);
        $form->handleRequest($request);
        $result = null;

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ContentVisibilityUpdateData $data) {
                $contentInfo = $data->getContentInfo();
                $contentName = $this->translationHelper->getTranslatedContentNameByContentInfo($contentInfo);
                $desiredVisibility = $data->getVisible();
                $location = $data->getLocation();

                if ($contentInfo->isHidden && $desiredVisibility === false) {
                    $this->notificationHandler->success(
                        /** @Desc("Content item '%name%' is already hidden.") */
                        'content.hide.already_hidden',
                        ['%name%' => $contentName],
                        'ibexa_content'
                    );
                }

                if (!$contentInfo->isHidden && $desiredVisibility === true) {
                    $this->notificationHandler->success(
                        /** @Desc("Content item '%name%' is already visible.") */
                        'content.reveal.already_visible',
                        ['%name%' => $contentName],
                        'ibexa_content'
                    );
                }

                if (!$contentInfo->isHidden && $desiredVisibility === false) {
                    $this->contentService->hideContent($contentInfo);

                    $this->notificationHandler->success(
                        /** @Desc("Content item '%name%' hidden.") */
                        'content.hide.success',
                        ['%name%' => $contentName],
                        'ibexa_content'
                    );
                }

                if ($contentInfo->isHidden && $desiredVisibility === true) {
                    $this->contentService->revealContent($contentInfo);

                    $this->notificationHandler->success(
                        /** @Desc("Content item '%name%' revealed.") */
                        'content.reveal.success',
                        ['%name%' => $contentName],
                        'ibexa_content'
                    );
                }

                return $location === null ? $this->redirectToRoute('ibexa.dashboard') : $this->redirectToLocation($location);
            });
        }

        return $result instanceof Response ? $result : $this->redirectToRoute('ibexa.dashboard');
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     * @param string|null $languageCode
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function checkEditPermissionAction(Content $content, ?string $languageCode): JsonResponse
    {
        $targets = [];

        if (null !== $languageCode) {
            $targets[] = (new Target\Builder\VersionBuilder())->translateToAnyLanguageOf([$languageCode])->build();
        }

        $canEdit = $this->permissionResolver->canUser(
            'content',
            'edit',
            $content,
            $targets
        );

        $lookupLimitations = $this->permissionResolver->lookupLimitations(
            'content',
            'edit',
            $content,
            $targets,
            [Limitation::LANGUAGE]
        );

        $editLanguagesLimitationValues = $this->lookupLimitationsTransformer->getFlattenedLimitationsValues($lookupLimitations);

        $response = new JsonResponse();
        $response->setData([
            'canEdit' => $canEdit,
            'editLanguagesLimitationValues' => $canEdit ? $editLanguagesLimitationValues : [],
        ]);

        // Disable HTTP cache
        $response->setPrivate();
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('no-store', true);

        return $response;
    }

    public function relationViewAction(int $contentId): Response
    {
        try {
            $content = $this->contentService->loadContent($contentId);
        } catch (UnauthorizedException $exception) {
            return $this->render('@ibexadesign/content/relation_unauthorized.html.twig', [
                'contentId' => $contentId,
            ]);
        }

        return $this->render('@ibexadesign/content/relation.html.twig', [
            'content' => $content,
            'contentType' => $content->getContentType(),
        ]);
    }
}

class_alias(ContentController::class, 'EzSystems\EzPlatformAdminUiBundle\Controller\ContentController');
