<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Exception;
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
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException as APIRepositoryUnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
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

final class LocationController extends Controller
{
    public function __construct(
        private readonly TranslatableNotificationHandlerInterface $notificationHandler,
        private readonly TranslatorInterface $translator,
        private readonly LocationService $locationService,
        private readonly ContentService $contentService,
        private readonly TrashService $trashService,
        private readonly SectionService $sectionService,
        private readonly FormFactory $formFactory,
        private readonly SubmitHandler $submitHandler,
        private readonly Repository $repository,
        private readonly TranslationHelper $translationHelper
    ) {
    }

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
                    [
                        '%name%' => $location->getContentInfo()->getName(),
                        '%location%' => $newParentLocation->getContentInfo()->getName(),
                    ],
                    'ibexa_location'
                );

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $location->getContentId(),
                    'locationId' => $location->getId(),
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content.view', [
            'contentId' => $location->getContentId(),
            'locationId' => $location->getId(),
        ]);
    }

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
                if ($location === null) {
                    $this->notificationHandler->error(
                        /** @Desc("Location cannot be copied.") */
                        'location.copy.failure',
                        [],
                        'ibexa_location'
                    );
                }

                $newParentLocation = $data->getNewParentLocation();

                if (!$newParentLocation->getContent()->getContentType()->isContainer()) {
                    throw new InvalidArgumentException(
                        '$newParentLocation',
                        'Cannot copy the Location to a parent that is not a container'
                    );
                }

                $locationCreateStruct = $this->locationService->newLocationCreateStruct(
                    $newParentLocation->getId()
                );

                $copiedContent = $this->contentService->copyContent(
                    $location->getContentInfo(),
                    $locationCreateStruct
                );

                $newLocation = $this->locationService->loadLocation(
                    $copiedContent->getContentInfo()->getMainLocationId()
                );

                $this->notificationHandler->success(
                    /** @Desc("'%name%' copied to '%location%'") */
                    'location.copy.success',
                    ['%name%' => $location->getContentInfo()->name, '%location%' => $newParentLocation->getContentInfo()->name],
                    'ibexa_location'
                );

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $newLocation->getContentId(),
                    'locationId' => $newLocation->getId(),
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content.view', [
            'contentId' => $location->getContentId(),
            'locationId' => $location->getId(),
        ]);
    }

    public function copySubtreeAction(Request $request): Response
    {
        $form = $this->formFactory->copyLocationSubtree(
            new LocationCopySubtreeData(),
            $request->query->get('formName')
        );
        $form->handleRequest($request);

        $location = $form->getData()->getLocation();

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle(
                $form,
                function (LocationCopySubtreeData $data) use ($location): RedirectResponse {
                    $newParentLocation = $data->getNewParentLocation();

                    $copiedContent = $this->locationService->copySubtree(
                        $location,
                        $newParentLocation
                    );

                    $newLocation = $this->locationService->loadLocation(
                        $copiedContent->getContentInfo()->getMainLocationId()
                    );

                    $this->notificationHandler->success(
                        /** @Desc("Subtree '%name%' copied to Location '%location%'") */
                        'location.copy_subtree.success',
                        [
                            '%name%' => $location->getContentInfo()->getName(),
                            '%location%' => $newParentLocation->getContentInfo()->getName(),
                        ],
                        'ibexa_location'
                    );

                    return $this->redirectToLocation($newLocation);
                }
            );

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToLocation($location);
    }

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

                $childCount = $this->locationService->getLocationChildCount($currentLocation, 1);
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
                    [
                        '%name%' => $currentLocation->getContentInfo()->getName(),
                        '%location%' => $newLocation->getContentInfo()->getName(),
                    ],
                    'ibexa_location'
                );

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $currentLocation->getContentId(),
                    'locationId' => $newLocation->getId(),
                    '_fragment' => LocationsTab::URI_FRAGMENT,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content.view', [
            'contentId' => $location->getContentId(),
            'locationId' => $location->getId(),
            '_fragment' => LocationsTab::URI_FRAGMENT,
        ]);
    }

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
            $content->getVersionInfo(),
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
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function handleTrashLocation(LocationTrashData $data): RedirectResponse
    {
        $location = $data->getLocation();
        $parentLocation = $this->locationService->loadLocation(
            $location->parentLocationId
        );
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
        } catch (Exception $exception) {
            $this->repository->rollback();
            throw $exception;
        }

        $this->notificationHandler->success(
            $this->translator->trans(
                /** @Desc("Location '%name%' moved to Trash.") */
                'location.trash.success',
                ['%name%' => $location->getContentInfo()->getName()],
                'ibexa_location'
            )
        );

        return $this->redirectToLocation($parentLocation);
    }

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
                        ['%name%' => $location->getContentInfo()->getName()],
                        'ibexa_location'
                    );
                }

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $contentInfo->getId(),
                    'locationId' => $contentInfo->getMainLocationId(),
                    '_fragment' => LocationsTab::URI_FRAGMENT,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content.view', [
            'contentId' => $contentInfo->getId(),
            'locationId' => $contentInfo->getMainLocationId(),
            '_fragment' => LocationsTab::URI_FRAGMENT,
        ]);
    }

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
                        $locationCreateStruct = $this->locationService->newLocationCreateStruct(
                            $newLocation->getId()
                        );

                        $this->locationService->createLocation($contentInfo, $locationCreateStruct);

                        $this->notificationHandler->success(
                            /** @Desc("Location '%name%' created.") */
                            'location.create.success',
                            ['%name%' => $newLocation->getContentInfo()->getName()],
                            'ibexa_location',
                        );
                    }

                    $redirectUrl = $referer ?: $this->generateUrl(
                        'ibexa.content.view',
                        [
                            'contentId' => $contentInfo->getId(),
                            'locationId' => $contentInfo->getMainLocationId(),
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
            'contentId' => $contentInfo->getId(),
            'locationId' => $contentInfo->getMainLocationId(),
            '_fragment' => LocationsTab::URI_FRAGMENT,
        ]);
    }

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
                $contentName = $this->translationHelper->getTranslatedContentNameByContentInfo(
                    $location->getContentInfo()
                );

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
            foreach ($form->getErrors(true) as $formError) {
                $errors[] = $formError->getMessage();
            }

            return new JsonResponse(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(['message' => $message]);
    }

    public function updateAction(Request $request): Response
    {
        $form = $this->formFactory->updateLocation();
        $form->handleRequest($request);

        $location = $form->getData()->getLocation();

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (LocationUpdateData $data): RedirectResponse {
                $location = $data->getLocation();

                $locationUpdateStruct = new LocationUpdateStruct([
                    'sortField' => $data->getSortField(),
                    'sortOrder' => $data->getSortOrder(),
                ]);

                $this->locationService->updateLocation($location, $locationUpdateStruct);

                $this->notificationHandler->success(
                    /** @Desc("Location '%name%' updated.") */
                    'location.update.success',
                    ['%name%' => $location->getContentInfo()->getName()],
                    'ibexa_location'
                );

                return new RedirectResponse($this->generateUrl('ibexa.content.view', [
                    'contentId' => $location->getContentId(),
                    'locationId' => $location->getContentInfo()->getMainLocationId(),
                    '_fragment' => DetailsTab::URI_FRAGMENT,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        $contentInfo = $location->getContentInfo();

        return $this->redirectToRoute('ibexa.content.view', [
            'contentId' => $contentInfo->getId(),
            'locationId' => $contentInfo->getMainLocationId(),
            '_fragment' => DetailsTab::URI_FRAGMENT,
        ]);
    }

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
                    ['%name%' => $section->getName()],
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
