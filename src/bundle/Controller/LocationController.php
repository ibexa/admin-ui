<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\Content\Location\ContentLocationAddData;
use Ibexa\AdminUi\Form\Data\Content\Location\ContentLocationRemoveData;
use Ibexa\AdminUi\Form\Data\Location\LocationAssignSubtreeData;
use Ibexa\AdminUi\Form\Data\Location\LocationCopyData;
use Ibexa\AdminUi\Form\Data\Location\LocationCopySubtreeData;
use Ibexa\AdminUi\Form\Data\Location\LocationMoveData;
use Ibexa\AdminUi\Form\Data\Location\LocationSwapData;
use Ibexa\AdminUi\Form\Data\Location\LocationTrashData;
use Ibexa\AdminUi\Form\Data\Location\LocationUpdateData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Form\TrashLocationOptionProvider\HasUniqueAssetRelation;
use Ibexa\AdminUi\Form\Type\Location\LocationAssignSectionType;
use Ibexa\AdminUi\Tab\LocationView\DetailsTab;
use Ibexa\AdminUi\Tab\LocationView\LocationsTab;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException as APIRepositoryUnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\TrashService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationUpdateStruct;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Helper\TranslationHelper;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocationController extends Controller
{
    private TranslatableNotificationHandlerInterface $notificationHandler;

    private TranslatorInterface $translator;

    private ContentService $contentService;

    private LocationService $locationService;

    private ContentTypeService $contentTypeService;

    private TrashService $trashService;

    private SectionService $sectionService;

    private FormFactory $formFactory;

    private SubmitHandler $submitHandler;

    private PermissionResolver $permissionResolver;

    private Repository $repository;

    private TranslationHelper $translationHelper;

    /**
     * @param \Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface $notificationHandler
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\Contracts\Core\Repository\TrashService $trashService
     * @param \Ibexa\Contracts\Core\Repository\SectionService $sectionService
     * @param \Ibexa\AdminUi\Form\Factory\FormFactory $formFactory
     * @param \Ibexa\AdminUi\Form\SubmitHandler $submitHandler
     * @param \Ibexa\Contracts\Core\Repository\PermissionResolver $permissionResolver
     * @param \Ibexa\Contracts\Core\Repository\Repository $repository
     * @param \Ibexa\Core\Helper\TranslationHelper $translationHelper
     */
    public function __construct(
        TranslatableNotificationHandlerInterface $notificationHandler,
        TranslatorInterface $translator,
        LocationService $locationService,
        ContentTypeService $contentTypeService,
        ContentService $contentService,
        TrashService $trashService,
        SectionService $sectionService,
        FormFactory $formFactory,
        SubmitHandler $submitHandler,
        PermissionResolver $permissionResolver,
        Repository $repository,
        TranslationHelper $translationHelper
    ) {
        $this->notificationHandler = $notificationHandler;
        $this->translator = $translator;
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->trashService = $trashService;
        $this->sectionService = $sectionService;
        $this->formFactory = $formFactory;
        $this->submitHandler = $submitHandler;
        $this->permissionResolver = $permissionResolver;
        $this->repository = $repository;
        $this->translationHelper = $translationHelper;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moveAction(Request $request): Response
    {
        $form = $this->formFactory->moveLocation(
            new LocationMoveData(),
            $request->query->get('formName')
        );
        $form->handleRequest($request);

        $location = $form->getData()->getLocation();

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (LocationMoveData $data): RedirectResponse {
                $location = $data->getLocation();
                $newParentLocation = $data->getNewParentLocation();

                if (!$newParentLocation->getContent()->getContentType()->isContainer()) {
                    throw new InvalidArgumentException(
                        '$newParentLocation',
                        'Cannot move the Location to a parent that is not a container'
                    );
                }

                $this->locationService->moveSubtree($location, $newParentLocation);

                $this->notificationHandler->success(
                    /** @Desc("'%name%' moved to '%location%'") */
                    'location.move.success',
                    ['%name%' => $location->getContentInfo()->name, '%location%' => $newParentLocation->getContentInfo()->name],
                    'ibexa_location'
                );

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $location->contentId,
                    'locationId' => $location->id,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content.view', [
            'contentId' => $location->contentId,
            'locationId' => $location->id,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function copyAction(Request $request): Response
    {
        $form = $this->formFactory->copyLocation(
            new LocationCopyData(),
            $request->query->get('formName')
        );
        $form->handleRequest($request);

        $location = $form->getData()->getLocation();

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (LocationCopyData $data): RedirectResponse {
                $location = $data->getLocation();
                $newParentLocation = $data->getNewParentLocation();

                if (!$newParentLocation->getContent()->getContentType()->isContainer()) {
                    throw new InvalidArgumentException(
                        '$newParentLocation',
                        'Cannot copy the Location to a parent that is not a container'
                    );
                }

                $locationCreateStruct = $this->locationService->newLocationCreateStruct($newParentLocation->id);
                $copiedContent = $this->contentService->copyContent(
                    $location->contentInfo,
                    $locationCreateStruct
                );

                $newLocation = $this->locationService->loadLocation($copiedContent->contentInfo->mainLocationId);

                $this->notificationHandler->success(
                    /** @Desc("'%name%' copied to '%location%'") */
                    'location.copy.success',
                    ['%name%' => $location->getContentInfo()->name, '%location%' => $newParentLocation->getContentInfo()->name],
                    'ibexa_location'
                );

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $newLocation->contentId,
                    'locationId' => $newLocation->id,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content.view', [
            'contentId' => $location->contentId,
            'locationId' => $location->id,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function copySubtreeAction(Request $request): Response
    {
        $form = $this->formFactory->copyLocationSubtree(
            new LocationCopySubtreeData(),
            $request->query->get('formName')
        );
        $form->handleRequest($request);

        $location = $form->getData()->getLocation();

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (LocationCopySubtreeData $data) use ($location): RedirectResponse {
                $newParentLocation = $data->getNewParentLocation();

                $copiedContent = $this->locationService->copySubtree(
                    $location,
                    $newParentLocation
                );

                $newLocation = $this->locationService->loadLocation($copiedContent->contentInfo->mainLocationId);

                $this->notificationHandler->success(
                    /** @Desc("Subtree '%name%' copied to Location '%location%'") */
                    'location.copy_subtree.success',
                    [
                        '%name%' => $location->getContentInfo()->name,
                        '%location%' => $newParentLocation->getContentInfo()->name,
                    ],
                    'ibexa_location'
                );

                return $this->redirectToLocation($newLocation);
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToLocation($location);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function swapAction(Request $request): Response
    {
        $form = $this->formFactory->swapLocation(
            new LocationSwapData()
        );
        $form->handleRequest($request);

        $location = $form->getData()->getCurrentLocation();

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (LocationSwapData $data): RedirectResponse {
                $currentLocation = $data->getCurrentLocation();
                $newLocation = $data->getNewLocation();

                $childCount = $this->locationService->getLocationChildCount($currentLocation);
                $contentType = $newLocation->getContent()->getContentType();

                if (!$contentType->isContainer() && $childCount) {
                    throw new \InvalidArgumentException(
                        'Cannot swap a Location that has sub-items with a Location that is not a container'
                    );
                }
                $this->locationService->swapLocation($currentLocation, $newLocation);

                $this->notificationHandler->success(
                    /** @Desc("Location '%name%' swapped with Location '%location%'") */
                    'location.swap.success',
                    ['%name%' => $currentLocation->getContentInfo()->name, '%location%' => $newLocation->getContentInfo()->name],
                    'ibexa_location'
                );

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $currentLocation->contentId,
                    'locationId' => $newLocation->id,
                    '_fragment' => LocationsTab::URI_FRAGMENT,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content.view', [
            'contentId' => $location->contentId,
            'locationId' => $location->id,
            '_fragment' => LocationsTab::URI_FRAGMENT,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function trashAction(Request $request): Response
    {
        $form = $this->formFactory->trashLocation(
            new LocationTrashData(),
            $request->query->get('formName')
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (LocationTrashData $data): RedirectResponse {
                return $this->handleTrashLocation($data);
            });

            if ($result instanceof Response) {
                return $result;
            }

            $data = $form->getData();
            $location = $data->getLocation();

            if ($location !== null) {
                return $this->redirectToLocation($location);
            }
        }

        return $this->redirectToRoute('ibexa.trash.list');
    }

    private function trashRelatedAsset(ContentInfo $contentInfo): void
    {
        $content = $this->contentService->loadContentByContentInfo($contentInfo);
        $relations = $this->contentService->loadRelationList(
            $content->versionInfo,
            0,
            1
        );

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\RelationListItem $relation */
        $relation = $relations->items[0];
        if (!$relation->hasRelation()) {
            return;
        }
        $mainLocationId = $relation->getRelation()->getDestinationContentInfo()->getMainLocationId();
        if ($mainLocationId === null) {
            return;
        }
        $imageLocation = $this->locationService->loadLocation($mainLocationId);
        $this->trashService->trash($imageLocation);
    }

    /**
     * @param \Ibexa\AdminUi\Form\Data\Location\LocationTrashData $data
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function handleTrashLocation(LocationTrashData $data): RedirectResponse
    {
        $location = $data->getLocation();
        $parentLocation = $this->locationService->loadLocation($location->parentLocationId);
        $trashOptions = $data->getTrashOptions();

        $this->repository->beginTransaction();
        try {
            if (isset($trashOptions[HasUniqueAssetRelation::TRASH_ASSETS])
                && HasUniqueAssetRelation::RADIO_SELECT_TRASH_WITH_ASSETS === $trashOptions[HasUniqueAssetRelation::TRASH_ASSETS]
            ) {
                $this->trashRelatedAsset($location->getContentInfo());
            }
            $this->trashService->trash($location);
            $this->repository->commit();
        } catch (\Exception $exception) {
            $this->repository->rollback();
            throw $exception;
        }

        $this->notificationHandler->success(
            $this->translator->trans(
                /** @Desc("Location '%name%' moved to Trash.") */
                'location.trash.success',
                ['%name%' => $location->getContentInfo()->name],
                'ibexa_location'
            )
        );

        return $this->redirectToLocation($parentLocation);
    }

    /**
     * Handles removing locations assigned to content item based on submitted form.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Request $request): Response
    {
        $form = $this->formFactory->removeLocation(
            new ContentLocationRemoveData()
        );
        $form->handleRequest($request);

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo */
        $contentInfo = $form->getData()->getContentInfo();

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ContentLocationRemoveData $data): RedirectResponse {
                $contentInfo = $data->getContentInfo();

                foreach ($data->getLocations() as $locationId => $selected) {
                    $location = $this->locationService->loadLocation($locationId);
                    $this->trashService->trash($location);

                    $this->notificationHandler->success(
                        /** @Desc("Location '%name%' removed.") */
                        'location.delete.success',
                        ['%name%' => $location->getContentInfo()->name],
                        'ibexa_location'
                    );
                }

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $contentInfo->id,
                    'locationId' => $contentInfo->mainLocationId,
                    '_fragment' => LocationsTab::URI_FRAGMENT,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content.view', [
            'contentId' => $contentInfo->id,
            'locationId' => $contentInfo->mainLocationId,
            '_fragment' => LocationsTab::URI_FRAGMENT,
        ]);
    }

    /**
     * Handles assigning new location to the content item based on submitted form.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request): Response
    {
        $form = $this->formFactory->addLocation(
            new ContentLocationAddData()
        );
        $form->handleRequest($request);

        $contentInfo = $form->getData()->getContentInfo();

        $referer = $request->headers->get('Referer');

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle(
                $form,
                function (ContentLocationAddData $data) use ($referer): RedirectResponse {
                    $contentInfo = $data->getContentInfo();

                    foreach ($data->getNewLocations() as $newLocation) {
                        $locationCreateStruct = $this->locationService->newLocationCreateStruct($newLocation->id);
                        $this->locationService->createLocation($contentInfo, $locationCreateStruct);

                        $this->notificationHandler->success(
                            /** @Desc("Location '%name%' created.") */
                            'location.create.success',
                            ['%name%' => $newLocation->getContentInfo()->name],
                            'ibexa_location',
                        );
                    }

                    $redirectUrl = $referer ?: $this->generateUrl(
                        'ibexa.content.view',
                        [
                            'contentId' => $contentInfo->id,
                            'locationId' => $contentInfo->mainLocationId,
                            '_fragment' => LocationsTab::URI_FRAGMENT,
                        ],
                    );

                    return new RedirectResponse($redirectUrl);
                }
            );

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content.view', [
            'contentId' => $contentInfo->id,
            'locationId' => $contentInfo->mainLocationId,
            '_fragment' => LocationsTab::URI_FRAGMENT,
        ]);
    }

    /**
     * Handles toggling visibility location of a content item based on submitted form.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateVisibilityAction(Request $request): Response
    {
        $form = $this->formFactory->updateVisibilityLocation();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
            $location = $data->getLocation();
            $hidden = $data->getHidden();

            try {
                $contentName = $this->translationHelper->getTranslatedContentNameByContentInfo($location->getContentInfo());

                if ($hidden) {
                    $this->locationService->hideLocation($location);
                    $message = $this->translator->trans(
                        /** @Desc("Location '%name%' hidden.") */
                        'location.update_success.success.hidden',
                        ['%name%' => $contentName],
                        'ibexa_location'
                    );
                } else {
                    $this->locationService->unhideLocation($location);
                    $message = $this->translator->trans(
                        /** @Desc("Location '%name%' revealed.") */
                        'location.update_success.success.unhidden',
                        ['%name%' => $contentName],
                        'ibexa_location'
                    );
                }
            } catch (APIRepositoryUnauthorizedException $e) {
                return new JsonResponse(['errors' => [$e->getMessage()]], Response::HTTP_UNAUTHORIZED);
            }
        } else {
            $errors = [];
            foreach ($form->getErrors(true, true) as $formError) {
                $errors[] = $formError->getMessage();
            }

            return new JsonResponse(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(['message' => $message]);
    }

    /**
     * Handles update existing location.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request): Response
    {
        $form = $this->formFactory->updateLocation();
        $form->handleRequest($request);

        $location = $form->getData()->getLocation();

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (LocationUpdateData $data): RedirectResponse {
                $location = $data->getLocation();

                $locationUpdateStruct = new LocationUpdateStruct(['sortField' => $data->getSortField(), 'sortOrder' => $data->getSortOrder()]);
                $this->locationService->updateLocation($location, $locationUpdateStruct);

                $this->notificationHandler->success(
                    /** @Desc("Location '%name%' updated.") */
                    'location.update.success',
                    ['%name%' => $location->getContentInfo()->name],
                    'ibexa_location'
                );

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $location->contentId,
                    'locationId' => $location->getContentInfo()->mainLocationId,
                    '_fragment' => DetailsTab::URI_FRAGMENT,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        $contentInfo = $location->getContentInfo();

        return $this->redirectToRoute('ibexa.content.view', [
            'contentId' => $contentInfo->id,
            'locationId' => $contentInfo->mainLocationId,
            '_fragment' => DetailsTab::URI_FRAGMENT,
        ]);
    }

    /**
     * Handles assigning section to subtree.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function assignSectionAction(Request $request): Response
    {
        $form = $this->createForm(LocationAssignSectionType::class, new LocationAssignSubtreeData());
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (LocationAssignSubtreeData $data): RedirectResponse {
                $section = $data->getSection();
                /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $location */
                $location = $data->getLocation();

                $this->sectionService->assignSectionToSubtree($location, $section);

                $this->notificationHandler->success(
                    /** @Desc("Subtree assigned to Section '%name%'") */
                    'location.assign_section.success',
                    ['%name%' => $section->name],
                    'ibexa_location'
                );

                return $this->redirectToLocation($location, DetailsTab::URI_FRAGMENT);
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        if (($location = $form->getData()->getLocation()) !== null) {
            return $this->redirectToLocation($location, DetailsTab::URI_FRAGMENT);
        }

        return $this->redirectToRoute('ibexa.dashboard');
    }
}
