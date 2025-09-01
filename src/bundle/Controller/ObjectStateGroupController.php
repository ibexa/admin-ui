<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\ObjectState\ObjectStateGroupCreateData;
use Ibexa\AdminUi\Form\Data\ObjectState\ObjectStateGroupDeleteData;
use Ibexa\AdminUi\Form\Data\ObjectState\ObjectStateGroupsDeleteData;
use Ibexa\AdminUi\Form\Data\ObjectState\ObjectStateGroupUpdateData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ObjectStateGroupController extends Controller
{
    public function __construct(
        private readonly TranslatableNotificationHandlerInterface $notificationHandler,
        private readonly ObjectStateService $objectStateService,
        private readonly FormFactory $formFactory,
        private readonly SubmitHandler $submitHandler,
        private readonly ConfigResolverInterface $configResolver
    ) {
    }

    public function listAction(): Response
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup[] $objectStateGroups */
        $objectStateGroups = $this->objectStateService->loadObjectStateGroups();
        $emptyObjectStateGroups = [];

        foreach ($objectStateGroups as $group) {
            $emptyObjectStateGroups[$group->id] = empty($this->objectStateService->loadObjectStates($group));
        }

        $deleteObjectStateGroupsForm = $this->formFactory->deleteObjectStateGroups(
            new ObjectStateGroupsDeleteData($this->getObjectStateGroupsIds($objectStateGroups))
        );

        return $this->render('@ibexadesign/object_state/object_state_group/list.html.twig', [
            'can_administrate' => $this->isGranted(new Attribute('state', 'administrate')),
            'object_state_groups' => $objectStateGroups,
            'empty_object_state_groups' => $emptyObjectStateGroups,
            'form_state_groups_delete' => $deleteObjectStateGroupsForm,
        ]);
    }

    public function viewAction(ObjectStateGroup $objectStateGroup): Response
    {
        $deleteForm = $this->formFactory->deleteObjectStateGroup(
            new ObjectStateGroupDeleteData($objectStateGroup)
        )->createView();

        return $this->render('@ibexadesign/object_state/object_state_group/view.html.twig', [
            'can_administrate' => $this->isGranted(new Attribute('state', 'administrate')),
            'object_state_group' => $objectStateGroup,
            'delete_form' => $deleteForm,
        ]);
    }

    public function addAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('state', 'administrate'));
        $languages = $this->configResolver->getParameter('languages');
        $defaultLanguageCode = reset($languages);

        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->formFactory->createObjectStateGroup(
            new ObjectStateGroupCreateData()
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle(
                $form,
                function (ObjectStateGroupCreateData $data) use ($defaultLanguageCode): Response {
                    $createStruct = $this->objectStateService->newObjectStateGroupCreateStruct(
                        $data->getIdentifier()
                    );
                    $createStruct->defaultLanguageCode = $defaultLanguageCode;
                    $createStruct->names = [$defaultLanguageCode => $data->getName()];
                    $group = $this->objectStateService->createObjectStateGroup($createStruct);

                    $this->notificationHandler->success(
                        /** @Desc("Object state group '%name%' created.") */
                        'object_state_group.create.success',
                        ['%name%' => $data->getName()],
                        'ibexa_object_state'
                    );

                    return $this->redirectToRoute('ibexa.object_state.group.view', [
                        'objectStateGroupId' => $group->id,
                    ]);
                }
            );

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->render('@ibexadesign/object_state/object_state_group/add.html.twig', [
            'form' => $form,
        ]);
    }

    public function deleteAction(Request $request, ObjectStateGroup $group): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('state', 'administrate'));
        $form = $this->formFactory->deleteObjectStateGroup(
            new ObjectStateGroupDeleteData($group)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ObjectStateGroupDeleteData $data): void {
                $group = $data->getObjectStateGroup();
                if ($group === null) {
                    return;
                }

                $this->objectStateService->deleteObjectStateGroup($group);

                $this->notificationHandler->success(
                    /** @Desc("Object state group '%name%' deleted.") */
                    'object_state_group.delete.success',
                    ['%name%' => $group->getName()],
                    'ibexa_object_state'
                );
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.object_state.groups.list');
    }

    public function bulkDeleteAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('state', 'administrate'));
        $form = $this->formFactory->deleteObjectStateGroups(
            new ObjectStateGroupsDeleteData()
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ObjectStateGroupsDeleteData $data): void {
                foreach ($data->getObjectStateGroups() ?? [] as $objectStateGroupId => $selected) {
                    $objectStateGroup = $this->objectStateService->loadObjectStateGroup($objectStateGroupId);
                    $this->objectStateService->deleteObjectStateGroup($objectStateGroup);

                    $this->notificationHandler->success(
                        /** @Desc("Object state group '%name%' deleted.") */
                        'object_state_group.delete.success',
                        ['%name%' => $objectStateGroup->getName()],
                        'ibexa_object_state'
                    );
                }
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.object_state.groups.list');
    }

    public function updateAction(Request $request, ObjectStateGroup $group): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('state', 'administrate'));
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->formFactory->updateObjectStateGroup(
            $group,
            new ObjectStateGroupUpdateData($group)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ObjectStateGroupUpdateData $data): Response {
                $group = $data->getObjectStateGroup();
                $updateStruct = $this->objectStateService->newObjectStateGroupUpdateStruct();
                $updateStruct->identifier = $data->getIdentifier();
                $updateStruct->names[$group->mainLanguageCode] = $data->getName();

                $updatedGroup = $this->objectStateService->updateObjectStateGroup($group, $updateStruct);

                $this->notificationHandler->success(
                    /** @Desc("Object state group '%name%' updated.") */
                    'object_state_group.update.success',
                    ['%name%' => $updatedGroup->getName()],
                    'ibexa_object_state'
                );

                return $this->redirectToRoute('ibexa.object_state.group.view', [
                    'objectStateGroupId' => $group->id,
                ]);
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->render('@ibexadesign/object_state/object_state_group/edit.html.twig', [
            'object_state_group' => $group,
            'form' => $form,
        ]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup[] $groups
     *
     * @return array<int, mixed>
     */
    private function getObjectStateGroupsIds(array $groups): array
    {
        $groupsIds = array_column($groups, 'id');

        return array_combine($groupsIds, array_fill_keys($groupsIds, false));
    }
}
