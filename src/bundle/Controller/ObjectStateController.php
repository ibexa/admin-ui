<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\ObjectState\ContentObjectStateUpdateData;
use Ibexa\AdminUi\Form\Data\ObjectState\ObjectStateCreateData;
use Ibexa\AdminUi\Form\Data\ObjectState\ObjectStateDeleteData;
use Ibexa\AdminUi\Form\Data\ObjectState\ObjectStatesDeleteData;
use Ibexa\AdminUi\Form\Data\ObjectState\ObjectStateUpdateData;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Form\Type\ObjectState\ContentObjectStateUpdateType;
use Ibexa\AdminUi\Form\Type\ObjectState\ObjectStateCreateType;
use Ibexa\AdminUi\Form\Type\ObjectState\ObjectStateDeleteType;
use Ibexa\AdminUi\Form\Type\ObjectState\ObjectStatesDeleteType;
use Ibexa\AdminUi\Form\Type\ObjectState\ObjectStateUpdateType;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use Symfony\Component\Form\Button;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ObjectStateController extends Controller
{
    /** @var \Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface */
    private $notificationHandler;

    /** @var \Ibexa\Contracts\Core\Repository\ObjectStateService */
    private $objectStateService;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $formFactory;

    /** @var \Ibexa\AdminUi\Form\SubmitHandler */
    private $submitHandler;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    public function __construct(
        TranslatableNotificationHandlerInterface $notificationHandler,
        ObjectStateService $objectStateService,
        FormFactoryInterface $formFactory,
        SubmitHandler $submitHandler,
        PermissionResolver $permissionResolver,
        ConfigResolverInterface $configResolver
    ) {
        $this->notificationHandler = $notificationHandler;
        $this->objectStateService = $objectStateService;
        $this->formFactory = $formFactory;
        $this->submitHandler = $submitHandler;
        $this->permissionResolver = $permissionResolver;
        $this->configResolver = $configResolver;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(ObjectStateGroup $objectStateGroup): Response
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState[] $objectStates */
        $objectStates = $this->objectStateService->loadObjectStates($objectStateGroup);

        $deleteObjectStatesForm = $this->formFactory->create(
            ObjectStatesDeleteType::class,
            new ObjectStatesDeleteData($this->getObjectStatesIds($objectStates))
        );

        $unusedObjectStates = [];

        foreach ($objectStates as $state) {
            $unusedObjectStates[$state->id] = empty($this->objectStateService->getContentCount($state));
        }

        return $this->render('@ibexadesign/object_state/list.html.twig', [
            'can_administrate' => $this->isGranted(new Attribute('state', 'administrate')),
            'object_state_group' => $objectStateGroup,
            'object_states' => $objectStates,
            'unused_object_states' => $unusedObjectStates,
            'form_states_delete' => $deleteObjectStatesForm->createView(),
        ]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState $objectState
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(ObjectState $objectState): Response
    {
        $deleteForm = $this->formFactory->create(
            ObjectStateDeleteType::class,
            new ObjectStateDeleteData($objectState)
        )->createView();

        return $this->render('@ibexadesign/object_state/view.html.twig', [
            'can_administrate' => $this->isGranted(new Attribute('state', 'administrate')),
            'object_state_group' => $objectState->getObjectStateGroup(),
            'object_state' => $objectState,
            'delete_form' => $deleteForm,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request, ObjectStateGroup $objectStateGroup): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('state', 'administrate'));
        $languages = $this->configResolver->getParameter('languages');
        $defaultLanguageCode = reset($languages);

        $form = $this->formFactory->create(
            ObjectStateCreateType::class,
            new ObjectStateCreateData()
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle(
                $form,
                function (ObjectStateCreateData $data) use ($defaultLanguageCode, $objectStateGroup, $form) {
                    $createStruct = $this->objectStateService->newObjectStateCreateStruct(
                        $data->getIdentifier()
                    );
                    $createStruct->defaultLanguageCode = $defaultLanguageCode;
                    $createStruct->names = [$defaultLanguageCode => $data->getName()];
                    $objectState = $this->objectStateService->createObjectState($objectStateGroup, $createStruct);

                    $this->notificationHandler->success(
                        /** @Desc("Object state '%name%' created.") */
                        'object_state.create.success',
                        ['%name%' => $data->getName()],
                        'object_state'
                    );

                    if ($form->getClickedButton() instanceof Button
                        && $form->getClickedButton()->getName() === ObjectStateCreateType::BTN_CREATE_AND_EDIT
                    ) {
                        return $this->redirectToRoute('ibexa.object_state.state.update', [
                            'objectStateId' => $objectState->id,
                        ]);
                    }

                    return $this->redirectToRoute('ibexa.object_state.state.view', [
                        'objectStateId' => $objectState->id,
                    ]);
                }
            );
            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->render('@ibexadesign/object_state/add.html.twig', [
            'object_state_group' => $objectStateGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState $objectState
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, ObjectState $objectState): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('state', 'administrate'));
        $form = $this->formFactory->create(
            ObjectStateDeleteType::class,
            new ObjectStateDeleteData($objectState)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ObjectStateDeleteData $data) {
                $objectState = $data->getObjectState();
                $this->objectStateService->deleteObjectState($objectState);

                $this->notificationHandler->success(
                    /** @Desc("Object state '%name%' deleted.") */
                    'object_state.delete.success',
                    ['%name%' => $objectState->getName()],
                    'object_state'
                );
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.object_state.group.view', [
            'objectStateGroupId' => $objectState->getObjectStateGroup()->id,
        ]);
    }

    /**
     * Handles removing object state groups based on submitted form.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $objectStateGroupId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bulkDeleteAction(Request $request, int $objectStateGroupId): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('state', 'administrate'));
        $form = $this->formFactory->create(
            ObjectStatesDeleteType::class,
            new ObjectStatesDeleteData()
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ObjectStatesDeleteData $data) {
                foreach ($data->getObjectStates() as $objectStateId => $selected) {
                    $objectState = $this->objectStateService->loadObjectState($objectStateId);
                    $this->objectStateService->deleteObjectState($objectState);

                    $this->notificationHandler->success(
                        /** @Desc("Object state '%name%' deleted.") */
                        'object_state.delete.success',
                        ['%name%' => $objectState->getName()],
                        'object_state'
                    );
                }
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.object_state.group.view', [
            'objectStateGroupId' => $objectStateGroupId,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState $objectState
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request, ObjectState $objectState): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('state', 'administrate'));
        $form = $this->formFactory->create(
            ObjectStateUpdateType::class,
            new ObjectStateUpdateData($objectState)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ObjectStateUpdateData $data) use ($form) {
                $objectState = $data->getObjectState();
                $updateStruct = $this->objectStateService->newObjectStateUpdateStruct();
                $updateStruct->identifier = $data->getIdentifier();
                $updateStruct->names[$objectState->mainLanguageCode] = $data->getName();

                $updatedObjectState = $this->objectStateService->updateObjectState($objectState, $updateStruct);

                $this->notificationHandler->success(
                    /** @Desc("Object state '%name%' updated.") */
                    'object_state.update.success',
                    ['%name%' => $updatedObjectState->getName()],
                    'object_state'
                );

                if ($form->getClickedButton() instanceof Button
                    && $form->getClickedButton()->getName() === ObjectStateUpdateType::BTN_SAVE
                ) {
                    return $this->redirectToRoute('ibexa.object_state.state.update', [
                        'objectStateId' => $objectState->id,
                    ]);
                }

                return $this->redirectToRoute('ibexa.object_state.state.view', [
                    'objectStateId' => $objectState->id,
                ]);
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->render('@ibexadesign/object_state/edit.html.twig', [
            'object_state_group' => $objectState->getObjectStateGroup(),
            'object_state' => $objectState,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo $contentInfo
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function updateContentStateAction(
        Request $request,
        ContentInfo $contentInfo,
        ObjectStateGroup $objectStateGroup
    ): Response {
        if (!$this->permissionResolver->hasAccess('state', 'assign')) {
            $exception = $this->createAccessDeniedException();
            $exception->setAttributes('state');
            $exception->setSubject('assign');

            throw $exception;
        }

        $form = $this->formFactory->create(
            ContentObjectStateUpdateType::class,
            new ContentObjectStateUpdateData($contentInfo, $objectStateGroup)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ContentObjectStateUpdateData $data) {
                $contentInfo = $data->getContentInfo();
                $objectStateGroup = $data->getObjectStateGroup();
                $objectState = $data->getObjectState();
                $this->objectStateService->setContentState($contentInfo, $objectStateGroup, $objectState);

                $this->notificationHandler->success(
                    /** @Desc("Content item's Object state changed to '%name%'.") */
                    'content_object_state.update.success',
                    ['%name%' => $objectState->getName()],
                    'object_state'
                );
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content.view', [
            'contentId' => $contentInfo->id,
            'locationId' => $contentInfo->mainLocationId,
            '_fragment' => 'ibexa-tab-location-view-details',
        ]);
    }

    /**
     * @param array $states
     *
     * @return array
     */
    private function getObjectStatesIds(array $states): array
    {
        $statesIds = array_column($states, 'id');

        return array_combine($statesIds, array_fill_keys($statesIds, false));
    }
}

class_alias(ObjectStateController::class, 'EzSystems\EzPlatformAdminUiBundle\Controller\ObjectStateController');
