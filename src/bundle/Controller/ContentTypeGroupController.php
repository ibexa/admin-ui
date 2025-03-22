<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\ContentTypeGroup\ContentTypeGroupCreateData;
use Ibexa\AdminUi\Form\Data\ContentTypeGroup\ContentTypeGroupDeleteData;
use Ibexa\AdminUi\Form\Data\ContentTypeGroup\ContentTypeGroupsDeleteData;
use Ibexa\AdminUi\Form\Data\ContentTypeGroup\ContentTypeGroupUpdateData;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Form\Type\ContentTypeGroup\ContentTypeGroupCreateType;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Button;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentTypeGroupController extends Controller
{
    private TranslatableNotificationHandlerInterface $notificationHandler;

    private ContentTypeService $contentTypeService;

    private FormFactory $formFactory;

    private SubmitHandler $submitHandler;

    private ConfigResolverInterface $configResolver;

    public function __construct(
        TranslatableNotificationHandlerInterface $notificationHandler,
        ContentTypeService $contentTypeService,
        FormFactory $formFactory,
        SubmitHandler $submitHandler,
        ConfigResolverInterface $configResolver
    ) {
        $this->notificationHandler = $notificationHandler;
        $this->contentTypeService = $contentTypeService;
        $this->formFactory = $formFactory;
        $this->submitHandler = $submitHandler;
        $this->configResolver = $configResolver;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request): Response
    {
        $deletableContentTypeGroup = [];
        $count = [];

        $page = $request->query->getInt('page', 1);

        $pagerfanta = new Pagerfanta(
            new ArrayAdapter(iterator_to_array($this->contentTypeService->loadContentTypeGroups()))
        );

        $pagerfanta->setMaxPerPage($this->configResolver->getParameter('pagination.content_type_group_limit'));
        $pagerfanta->setCurrentPage(min($page, $pagerfanta->getNbPages()));

        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup[] $contentTypeGroupList */
        $contentTypeGroupList = $pagerfanta->getCurrentPageResults();

        $deleteContentTypeGroupsForm = $this->formFactory->deleteContentTypeGroups(
            new ContentTypeGroupsDeleteData($this->getContentTypeGroupsNumbers($contentTypeGroupList))
        );

        foreach ($contentTypeGroupList as $contentTypeGroup) {
            $contentTypesCount = count($this->contentTypeService->loadContentTypes($contentTypeGroup));
            $deletableContentTypeGroup[$contentTypeGroup->id] = !(bool)$contentTypesCount;
            $count[$contentTypeGroup->id] = $contentTypesCount;
        }

        return $this->render('@ibexadesign/content_type/content_type_group/list.html.twig', [
            'pager' => $pagerfanta,
            'form_content_type_groups_delete' => $deleteContentTypeGroupsForm,
            'deletable' => $deletableContentTypeGroup,
            'content_types_count' => $count,
            'can_create' => $this->isGranted(new Attribute('class', 'create')),
            'can_update' => $this->isGranted(new Attribute('class', 'update')),
            'can_delete' => $this->isGranted(new Attribute('class', 'delete')),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('class', 'create'));
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->formFactory->createContentTypeGroup(
            new ContentTypeGroupCreateData()
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ContentTypeGroupCreateData $data) use ($form): Response {
                $createStruct = $this->contentTypeService->newContentTypeGroupCreateStruct(
                    $data->getIdentifier()
                );
                $group = $this->contentTypeService->createContentTypeGroup($createStruct);

                $this->notificationHandler->success(
                    /** @Desc("Created content type group '%name%'.") */
                    'content_type_group.create.success',
                    ['%name%' => $data->getIdentifier()],
                    'ibexa_content_type'
                );

                if ($form->getClickedButton() instanceof Button
                    && $form->getClickedButton()->getName() === ContentTypeGroupCreateType::BTN_SAVE
                ) {
                    return $this->redirectToRoute('ibexa.content_type_group.update', [
                        'contentTypeGroupId' => $group->id,
                    ]);
                }

                return new RedirectResponse($this->generateUrl('ibexa.content_type_group.view', [
                    'contentTypeGroupId' => $group->id,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->render('@ibexadesign/content_type/content_type_group/create.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup $group
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request, ContentTypeGroup $group): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('class', 'update'));
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->formFactory->updateContentTypeGroup(
            new ContentTypeGroupUpdateData($group)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ContentTypeGroupUpdateData $data) use ($form): Response {
                $group = $data->getContentTypeGroup();
                $updateStruct = $this->contentTypeService->newContentTypeGroupUpdateStruct();
                $updateStruct->identifier = $data->getIdentifier();

                $this->contentTypeService->updateContentTypeGroup($group, $updateStruct);

                $this->notificationHandler->success(
                    /** @Desc("Updated content type group '%name%'.") */
                    'content_type_group.update.success',
                    ['%name%' => $group->identifier],
                    'ibexa_content_type'
                );

                if ($form->getClickedButton() instanceof Button
                    && $form->getClickedButton()->getName() === ContentTypeGroupCreateType::BTN_SAVE
                ) {
                    return $this->redirectToRoute('ibexa.content_type_group.update', [
                        'contentTypeGroupId' => $group->id,
                    ]);
                }

                return new RedirectResponse($this->generateUrl('ibexa.content_type_group.view', [
                    'contentTypeGroupId' => $group->id,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->render('@ibexadesign/content_type/content_type_group/edit.html.twig', [
            'content_type_group' => $group,
            'form' => $form,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup $group
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, ContentTypeGroup $group): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('class', 'delete'));
        $form = $this->formFactory->deleteContentTypeGroup(
            new ContentTypeGroupDeleteData($group)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ContentTypeGroupDeleteData $data): void {
                $group = $data->getContentTypeGroup();
                $this->contentTypeService->deleteContentTypeGroup($group);

                $this->notificationHandler->success(
                    /** @Desc("Deleted content type group '%name%'.") */
                    'content_type_group.delete.success',
                    ['%name%' => $group->identifier],
                    'ibexa_content_type'
                );
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content_type_group.list');
    }

    /**
     * Handles removing content type groups based on submitted form.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bulkDeleteAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('class', 'delete'));
        $form = $this->formFactory->deleteContentTypeGroups(
            new ContentTypeGroupsDeleteData()
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (ContentTypeGroupsDeleteData $data): void {
                foreach ($data->getContentTypeGroups() as $contentTypeGroupId => $selected) {
                    $group = $this->contentTypeService->loadContentTypeGroup($contentTypeGroupId);
                    $this->contentTypeService->deleteContentTypeGroup($group);

                    $this->notificationHandler->success(
                        /** @Desc("Deleted content type group '%name%'.") */
                        'content_type_group.delete.success',
                        ['%name%' => $group->identifier],
                        'ibexa_content_type'
                    );
                }
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.content_type_group.list');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup $group
     * @param int $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, ContentTypeGroup $group, int $page = 1): Response
    {
        return $this->render('@ibexadesign/content_type/content_type_group/index.html.twig', [
            'content_type_group' => $group,
            'page' => $page,
            'route_name' => $request->get('_route'),
            'can_create' => $this->isGranted(new Attribute('class', 'create')),
        ]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup[] $contentTypeGroups
     *
     * @return array
     */
    private function getContentTypeGroupsNumbers(array $contentTypeGroups): array
    {
        $contentTypeGroupsNumbers = array_column($contentTypeGroups, 'id');

        return array_combine($contentTypeGroupsNumbers, array_fill_keys($contentTypeGroupsNumbers, false));
    }
}
