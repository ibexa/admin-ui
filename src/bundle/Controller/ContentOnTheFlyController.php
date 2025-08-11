<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Event\Options;
use Ibexa\AdminUi\Form\ActionDispatcher\CreateContentOnTheFlyDispatcher;
use Ibexa\AdminUi\Specification\ContentType\ContentTypeIsUser;
use Ibexa\AdminUi\View\CreateContentOnTheFlyView;
use Ibexa\AdminUi\View\EditContentOnTheFlySuccessView;
use Ibexa\AdminUi\View\EditContentOnTheFlyView;
use Ibexa\ContentForms\Data\Mapper\ContentCreateMapper;
use Ibexa\ContentForms\Data\Mapper\ContentUpdateMapper;
use Ibexa\ContentForms\Form\ActionDispatcher\ActionDispatcherInterface;
use Ibexa\ContentForms\Form\Type\Content\ContentEditType;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Event\ContentProxyCreateEvent;
use Ibexa\Contracts\ContentForms\Content\Form\Provider\GroupedContentFormFieldsProviderInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions as ApiException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Base\Exceptions\BadStateException;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Ibexa\Core\MVC\Symfony\View\BaseView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ContentOnTheFlyController extends Controller
{
    private const string AUTOSAVE_ACTION_NAME = 'autosave';

    public function __construct(
        private readonly ContentService $contentService,
        private readonly LanguageService $languageService,
        private readonly LocationService $locationService,
        private readonly ContentTypeService $contentTypeService,
        private readonly PermissionResolver $permissionResolver,
        private readonly GroupedContentFormFieldsProviderInterface $groupedContentFormFieldsProvider,
        private readonly UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        private readonly CreateContentOnTheFlyDispatcher $createContentActionDispatcher,
        private readonly ConfigResolverInterface $configResolver,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ActionDispatcherInterface $contentActionDispatcher
    ) {
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function hasCreateAccessAction(
        string $languageCode,
        ContentType $contentType,
        Location $parentLocation
    ): JsonResponse {
        $response = new JsonResponse();

        try {
            $contentCreateStruct = $this->createContentCreateStruct($parentLocation, $contentType, $languageCode);
            $locationCreateStruct = $this->locationService->newLocationCreateStruct($parentLocation->id);

            if (
                !$this->permissionResolver->canUser(
                    'content',
                    'create',
                    $contentCreateStruct,
                    [$locationCreateStruct]
                )
            ) {
                throw new UnauthorizedException(
                    'content',
                    'create',
                    [
                        'contentTypeIdentifier' => $contentType->getIdentifier(),
                        'parentLocationId' => $locationCreateStruct->parentLocationId,
                        'languageCode' => $languageCode,
                    ]
                );
            }

            if (
                !$this->permissionResolver->canUser(
                    'content',
                    'publish',
                    $contentCreateStruct,
                    [$locationCreateStruct]
                )
            ) {
                throw new UnauthorizedException(
                    'content',
                    'publish',
                    [
                        'contentTypeIdentifier' => $contentType->getIdentifier(),
                        'parentLocationId' => $locationCreateStruct->parentLocationId,
                        'languageCode' => $languageCode,
                    ]
                );
            }

            $response->setData([
                'access' => true,
            ]);
        } catch (ApiException\UnauthorizedException $exception) {
            $response->setData([
                'access' => false,
                'message' => $exception->getMessage(),
            ]);
        }

        return $response;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function createContentAction(
        Request $request,
        string $languageCode,
        ContentType $contentType,
        Location $parentLocation
    ): BaseView|Response {
        if (
            (new ContentTypeIsUser($this->configResolver->getParameter('user_content_type_identifier')))
            ->isSatisfiedBy($contentType)
        ) {
            return $this->forward('Ibexa\Bundle\AdminUi\Controller\UserOnTheFlyController::createUserAction', [
                'languageCode' => $languageCode,
                'contentType' => $contentType,
                'parentLocation' => $parentLocation,
            ]);
        }

        /** @var \Ibexa\Contracts\AdminUi\Event\ContentProxyCreateEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new ContentProxyCreateEvent(
                $contentType,
                $languageCode,
                $parentLocation->id,
                new Options([
                    'isOnTheFly' => true,
                ])
            )
        );

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        $language = $this->languageService->loadLanguage($languageCode);

        $data = (new ContentCreateMapper())->mapToFormData($contentType, [
            'mainLanguageCode' => $language->languageCode,
            'parentLocation' => $this->locationService->newLocationCreateStruct($parentLocation->id),
        ]);

        $form = $this->createForm(ContentEditType::class, $data, [
            'languageCode' => $language->languageCode,
            'mainLanguageCode' => $language->languageCode,
            'contentCreateStruct' => $data,
            'drafts_enabled' => false,
            'intent' => 'create',
            'struct' => $data,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->getClickedButton()) {
            $this->createContentActionDispatcher->dispatchFormAction(
                $form,
                $data,
                $form->getClickedButton()->getName()
            );

            if ($response = $this->createContentActionDispatcher->getResponse()) {
                return $response;
            }
        }

        return new CreateContentOnTheFlyView('@ibexadesign/ui/on_the_fly/content_create_on_the_fly.html.twig', [
            'form' => $form->createView(),
            'language' => $language,
            'content_type' => $contentType,
            'parent_location' => $parentLocation,
            'grouped_fields' => $this->groupedContentFormFieldsProvider->getGroupedFields(
                $form->get('fieldsData')->all()
            ),
        ]);
    }

    /**
     * @throws \Ibexa\AdminUi\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Core\Base\Exceptions\BadStateException
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException
     */
    public function editContentAction(
        Request $request,
        string $languageCode,
        int $contentId,
        int $versionNo,
        ?int $locationId
    ): Response|EditContentOnTheFlySuccessView|EditContentOnTheFlyView {
        $content = $this->contentService->loadContent($contentId, [$languageCode], $versionNo);
        $versionInfo = $content->getVersionInfo();

        $location = null;
        if (!empty($locationId)) {
            $location = $this->locationService->loadLocation($locationId);
        }

        $contentType = $this->contentTypeService->loadContentType(
            $content->getContentInfo()->contentTypeId,
            $this->userLanguagePreferenceProvider->getPreferredLanguages()
        );

        if (
            (new ContentTypeIsUser($this->configResolver->getParameter('user_content_type_identifier')))
            ->isSatisfiedBy($contentType)
        ) {
            return $this->forward('Ibexa\Bundle\AdminUi\Controller\UserOnTheFlyController::editUserAction', [
                'languageCode' => $languageCode,
                'contentId' => $contentId,
                'versionNo' => $versionNo,
                'locationId' => $locationId,
            ]);
        }

        $language = $this->languageService->loadLanguage($languageCode);

        $contentUpdate = (new ContentUpdateMapper())->mapToFormData($content, [
            'languageCode' => $languageCode,
            'contentType' => $contentType,
        ]);

        $form = $this->createForm(
            ContentEditType::class,
            $contentUpdate,
            [
                'location' => $location,
                'languageCode' => $languageCode,
                'mainLanguageCode' => $content->getContentInfo()->getMainLanguageCode(),
                'content' => $content,
                'contentUpdateStruct' => $contentUpdate,
                'drafts_enabled' => true,
                'struct' => $contentUpdate,
            ]
        );

        $form->handleRequest($request);

        if (!$versionInfo->isDraft()) {
            throw new BadStateException('Version', 'The status is not draft');
        }

        if (null === $location && $content->getContentInfo()->isPublished()) {
            // assume main location if no location was provided
            $location = $content->getContentInfo()->getMainLocation();
        }

        if (null !== $location && $location->getContentId() !== $content->getId()) {
            throw new InvalidArgumentException('Location', 'The provided Location does not belong to the selected content');
        }

        if ($form->isSubmitted() && $form->isValid() && null !== $form->getClickedButton()) {
            $actionName = $form->getClickedButton()->getName();

            $actionDispatcher = $actionName === self::AUTOSAVE_ACTION_NAME
                ? $this->contentActionDispatcher
                : $this->createContentActionDispatcher;

            $actionDispatcher->dispatchFormAction(
                $form,
                $form->getData(),
                $actionName,
                ['referrerLocation' => $location]
            );

            if (!$location instanceof Location) {
                $contentInfo = $this->contentService->loadContentInfo($content->getId());

                if (null !== $contentInfo->getMainLocationId()) {
                    $location = $this->locationService->loadLocation($contentInfo->getMainLocationId());
                }
            }

            if ($actionDispatcher->getResponse()) {
                $view = new EditContentOnTheFlySuccessView(
                    '@ibexadesign/ui/on_the_fly/content_edit_response.html.twig'
                );

                $view->addParameters([
                    'locationId' => $location instanceof Location ? $location->getId() : null,
                ]);

                return $view;
            }
        }

        return $this->buildEditView($content, $language, $location, $form, $contentType);
    }

    private function createContentCreateStruct(
        Location $location,
        ContentType $contentType,
        string $language
    ): ContentCreateStruct {
        $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, $language);
        $contentCreateStruct->sectionId = $location->getContentInfo()->getSectionId();

        return $contentCreateStruct;
    }

    private function buildEditView(
        Content $content,
        Language $language,
        ?Location $location,
        FormInterface $form,
        ContentType $contentType
    ): EditContentOnTheFlyView {
        $view = new EditContentOnTheFlyView(
            '@ibexadesign/ui/on_the_fly/content_edit_on_the_fly.html.twig'
        );

        $view->setContent($content);
        $view->setLanguage($language);
        $view->setLocation($location);
        $view->setForm($form);

        $view->addParameters([
            'content' => $content,
            'location' => $location,
            'language' => $language,
            'content_type' => $contentType,
            'form' => $form->createView(),
            'grouped_fields' => $this->groupedContentFormFieldsProvider->getGroupedFields(
                $form->get('fieldsData')->all()
            ),
        ]);

        return $view;
    }
}
