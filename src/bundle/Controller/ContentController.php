<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

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
use Ibexa\AdminUi\Form\Type\Preview\VersionPreviewUrlChoiceType;
use Ibexa\AdminUi\Permission\LookupLimitationsTransformer;
use Ibexa\AdminUi\Siteaccess\SiteAccessNameGeneratorInterface;
use Ibexa\AdminUi\Siteaccess\SiteaccessResolverInterface;
use Ibexa\AdminUi\Specification\ContentIsUser;
use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Event\ContentEditEvent;
use Ibexa\Contracts\AdminUi\Event\ContentProxyCreateEvent;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\AdminUi\PreviewUrlResolver\VersionPreviewUrlResolverInterface;
use Ibexa\Contracts\Core\Limitation\Target;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LanguageService;
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
use Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ContentController extends Controller
{
    public function __construct(
        private readonly TranslatableNotificationHandlerInterface $notificationHandler,
        private readonly ContentService $contentService,
        private readonly FormFactory $formFactory,
        private readonly SubmitHandler $submitHandler,
        private readonly ContentMainLocationUpdateMapper $contentMainLocationUpdateMapper,
        private readonly SiteaccessResolverInterface $siteaccessResolver,
        private readonly LocationService $locationService,
        private readonly UserService $userService,
        private readonly PermissionResolver $permissionResolver,
        private readonly LookupLimitationsTransformer $lookupLimitationsTransformer,
        private readonly TranslationHelper $translationHelper,
        private readonly ConfigResolverInterface $configResolver,
        private readonly SiteAccessNameGeneratorInterface $siteAccessNameGenerator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly FormFactoryInterface $baseFormFactory,
        private readonly VersionPreviewUrlResolverInterface $previewUrlResolver,
        private readonly LanguageService $languageService,
        private readonly SiteAccessServiceInterface $siteAccessService
    ) {
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function createAction(Request $request): Response
    {
        $formName = $request->query->get('formName');
        $form = $this->formFactory->createContent(null, $formName);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ContentCreateData $data): RedirectResponse {
                $contentType = $data->getContentType();
                if ($contentType === null) {
                    throw new BadStateException(
                        '$contentType',
                        'Content type is not set'
                    );
                }

                $language = $data->getLanguage();
                $parentLocation = $data->getParentLocation();

                if (
                    (new ContentTypeIsUser($this->configResolver->getParameter('user_content_type_identifier')))
                    ->isSatisfiedBy($contentType)
                ) {
                    return $this->redirectToRoute('ibexa.user.create', [
                        'contentTypeIdentifier' => $contentType->getIdentifier(),
                        'language' => $language->getLanguageCode(),
                        'parentLocationId' => $parentLocation->getId(),
                    ]);
                }

                return $this->redirectToRoute('ibexa.content.create.proxy', [
                    'contentTypeIdentifier' => $contentType->getIdentifier(),
                    'languageCode' => $language->getLanguageCode(),
                    'parentLocationId' => $parentLocation->getId(),
                ]);
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.dashboard');
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
            'contentTypeIdentifier' => $contentType->getIdentifier(),
            'language' => $languageCode,
            'parentLocationId' => $parentLocationId,
        ]);
    }

    /**
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function editAction(Request $request): Response
    {
        /* @todo it shouldn't rely on keys from request */
        /** @var string[] $requestKeys */
        $requestKeys = $request->request->keys();
        $formName = $request->query->get(
            'formName',
            reset($requestKeys) ?: null
        );

        $form = $this->formFactory->contentEdit(null, $formName);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ContentEditData $data): Response {
                $contentInfo = $data->getContentInfo();
                $language = $data->getLanguage();
                $location = $data->getLocation();

                $content = $this->contentService->loadContent($contentInfo->getId());
                $versionInfo = $data->getVersionInfo() ?? $content->getVersionInfo();
                $versionNo = $versionInfo->getVersionNo();

                if ((new ContentIsUser($this->userService))->isSatisfiedBy($content)) {
                    return $this->redirectToRoute('ibexa.user.update', [
                        'contentId' => $contentInfo->getId(),
                        'versionNo' => $versionNo,
                        'language' => $language->getLanguageCode(),
                    ]);
                }

                /** @var \Ibexa\Contracts\AdminUi\Event\ContentEditEvent $event */
                $event = $this->eventDispatcher->dispatch(
                    new ContentEditEvent(
                        $content,
                        $versionInfo,
                        $language->getLanguageCode()
                    )
                );

                if ($event->hasResponse()) {
                    $response = $event->getResponse();
                    if ($response !== null) {
                        return $response;
                    }
                }

                if (!$versionInfo->isDraft()) {
                    $contentDraft = $this->contentService->createContentDraft($contentInfo, $versionInfo, null, $language);
                    $versionNo = $contentDraft->getVersionInfo()->getVersionNo();

                    $this->notificationHandler->success(
                        /** @Desc("Created a new draft for '%name%'.") */
                        'content.create_draft.success',
                        ['%name%' => $this->translationHelper->getTranslatedContentName($content)],
                        'ibexa_content'
                    );
                }

                return $this->redirectToRoute('ibexa.content.draft.edit', [
                    'contentId' => $contentInfo->getId(),
                    'versionNo' => $versionNo,
                    'language' => $language->getLanguageCode(),
                    'locationId' => null !== $location
                        ? $location->getId()
                        : $contentInfo->getMainLocationId(),
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
                'contentId' => $contentInfo->getId(),
                'locationId' => $contentInfo->getMainLocationId(),
            ]);
        }

        return $this->redirectToRoute('ibexa.dashboard');
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function updateMainLocationAction(Request $request): Response
    {
        $form = $this->formFactory->updateContentMainLocation();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ContentMainLocationUpdateData $data): RedirectResponse {
                $contentInfo = $data->contentInfo;

                $contentMetadataUpdateStruct = $this->contentMainLocationUpdateMapper->reverseMap($data);

                $this->contentService->updateContentMetadata($contentInfo, $contentMetadataUpdateStruct);

                $this->notificationHandler->success(
                    /** @Desc("Main Location for '%name%' updated.") */
                    'content.main_location_update.success',
                    ['%name%' => $contentInfo->name],
                    'ibexa_content'
                );

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $contentInfo->getId(),
                    'locationId' => $contentInfo->getMainLocationId(),
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
                'contentId' => $contentInfo->getId(),
                'locationId' => $contentInfo->getMainLocationId(),
                '_fragment' => 'ibexa-tab-location-view-locations',
            ]));
        }

        return $this->redirectToRoute('ibexa.dashboard');
    }

    public function previewAction(
        Request $request,
        Content $content,
        ?string $languageCode = null,
        ?int $versionNo = null,
        ?Location $location = null
    ): Response {
        $referrer = $request->query->get('referrer');

        if (null === $languageCode) {
            $languageCode = $content->getContentInfo()->getMainLanguageCode();
        }

        // nonpublished content should use parent location instead because location doesn't exist yet
        if (!$content->getContentInfo()->isPublished() && null === $content->getContentInfo()->getMainLocationId()) {
            $versionInfo = $this->contentService->loadVersionInfo($content->getContentInfo(), $versionNo);
            $parentLocations = $this->locationService->loadParentLocationsForDraftContent($versionInfo);
            $location = reset($parentLocations);
            $versionNo = null;
        }

        if (!$location instanceof Location) {
            $location = $this->locationService->loadLocation(
                $content->getContentInfo()->getMainLocationId()
            );
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

        $preselectedSiteAccessName = $request->query->get('preselectedSiteAccessName', reset($siteAccessesList));
        if (!array_key_exists($preselectedSiteAccessName, $siteAccessesList)) {
            $preselectedSiteAccessName = reset($siteAccessesList);
        }

        $versionInfo = $this->contentService->loadVersionInfo($content->getContentInfo(), $versionNo);
        $language = $this->languageService->loadLanguage($languageCode);

        $previewUrl = $this->previewUrlResolver->resolveUrl(
            $versionInfo,
            $location,
            $language,
            $this->siteAccessService->get($preselectedSiteAccessName)
        );

        $siteAccessSelector = $this->baseFormFactory->create(
            VersionPreviewUrlChoiceType::class,
            $previewUrl,
            [
                'location' => $location,
                'version_info' => $versionInfo,
                'language' => $language,
            ]
        );

        return $this->render('@ibexadesign/content/content_preview.html.twig', [
            'location' => $location,
            'content' => $content,
            'language_code' => $languageCode,
            'siteaccesses' => $siteAccessesList,
            'site_access_form' => $siteAccessSelector,
            'version_no' => $versionNo ?? $content->getVersionInfo()->getVersionNo(),
            'preselected_site_access' => $preselectedSiteAccessName,
            'referrer' => $referrer ?? 'content_draft_edit',
            'preview_url' => $previewUrl,
        ]);
    }

    public function updateMainTranslationAction(Request $request): Response
    {
        $form = $this->createForm(MainTranslationUpdateType::class, new MainTranslationUpdateData());
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (MainTranslationUpdateData $data): RedirectResponse {
                $content = $data->content;
                $contentInfo = $content->getContentInfo();
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
                    'contentId' => $contentInfo->getId(),
                    'locationId' => $contentInfo->getMainLocationId(),
                    '_fragment' => 'ibexa-tab-location-view-translations',
                ]));
            });
            if ($result instanceof Response) {
                return $result;
            }
        }
        /** @var \Ibexa\AdminUi\Form\Data\Content\Translation\MainTranslationUpdateData $data */
        $data = $form->getData();
        $contentInfo = $data->content;
        if (null !== $contentInfo) {
            return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                'contentId' => $contentInfo->getId(),
                'locationId' => $contentInfo->getContentInfo()->getMainLocationId(),
                '_fragment' => 'ibexa-tab-location-view-translations',
            ]));
        }

        return $this->redirectToRoute('ibexa.dashboard');
    }

    public function updateVisibilityAction(Request $request): Response
    {
        $formName = $request->query->get('formName');
        $form = $this->formFactory->updateVisibilityContent(null, $formName);
        $form->handleRequest($request);
        $result = null;

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ContentVisibilityUpdateData $data): RedirectResponse {
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
        } catch (UnauthorizedException) {
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
