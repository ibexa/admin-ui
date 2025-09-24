<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\Role\RoleCopyData;
use Ibexa\AdminUi\Form\Data\Role\RoleCreateData;
use Ibexa\AdminUi\Form\Data\Role\RoleDeleteData;
use Ibexa\AdminUi\Form\Data\Role\RolesDeleteData;
use Ibexa\AdminUi\Form\Data\Role\RoleUpdateData;
use Ibexa\AdminUi\Form\DataMapper\RoleCopyMapper;
use Ibexa\AdminUi\Form\DataMapper\RoleCreateMapper;
use Ibexa\AdminUi\Form\DataMapper\RoleUpdateMapper;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Form\Type\Role\RoleCopyType;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\Role;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RoleController extends Controller
{
    public function __construct(
        private readonly TranslatableNotificationHandlerInterface $notificationHandler,
        private readonly RoleService $roleService,
        private readonly RoleCreateMapper $roleCreateMapper,
        private readonly RoleCopyMapper $roleCopyMapper,
        private readonly RoleUpdateMapper $roleUpdateMapper,
        private readonly FormFactory $formFactory,
        private readonly SubmitHandler $submitHandler,
        private readonly ConfigResolverInterface $configResolver
    ) {
    }

    public function listAction(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $pagerfanta = new Pagerfanta(
            new ArrayAdapter(iterator_to_array($this->roleService->loadRoles()))
        );

        $pagerfanta->setMaxPerPage(
            $this->configResolver->getParameter('pagination.role_limit')
        );

        $pagerfanta->setCurrentPage(min($page, $pagerfanta->getNbPages()));

        /** @var \Ibexa\Contracts\Core\Repository\Values\User\Role[] $roles */
        $roles = $pagerfanta->getCurrentPageResults();

        $rolesNumbers = array_column($roles, 'id');

        $rolesDeleteData = new RolesDeleteData(
            array_combine($rolesNumbers, array_fill_keys($rolesNumbers, false))
        );

        $rolesDeleteForm = $this->formFactory->deleteRoles($rolesDeleteData);

        return $this->render('@ibexadesign/user/role/list.html.twig', [
            'form_roles_delete' => $rolesDeleteForm,
            'pager' => $pagerfanta,
            'can_create' => $this->isGranted(new Attribute('role', 'create')),
            'can_delete' => $this->isGranted(new Attribute('role', 'delete')),
            'can_update' => $this->isGranted(new Attribute('role', 'update')),
            'can_assign' => $this->isGranted(new Attribute('role', 'assign')),
        ]);
    }

    public function viewAction(Request $request, Role $role, int $policyPage = 1, int $assignmentPage = 1): Response
    {
        $deleteForm = $this->formFactory->deleteRole(
            new RoleDeleteData($role)
        );

        return $this->render('@ibexadesign/user/role/index.html.twig', [
            'role' => $role,
            'delete_form' => $deleteForm,
            'route_name' => $request->get('_route'),
            'policy_page' => $policyPage,
            'assignment_page' => $assignmentPage,
        ]);
    }

    public function createAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('role', 'create'));
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->formFactory->createRole();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (RoleCreateData $data): Response {
                $roleCreateStruct = $this->roleCreateMapper->reverseMap($data);
                $roleDraft = $this->roleService->createRole($roleCreateStruct);
                $this->roleService->publishRoleDraft($roleDraft);

                $this->notificationHandler->success(
                    /** @Desc("Role '%role%' created.") */
                    'role.create.success',
                    ['%role%' => $roleDraft->identifier],
                    'ibexa_role'
                );

                return new RedirectResponse($this->generateUrl('ibexa.role.view', [
                    'roleId' => $roleDraft->id,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->render('@ibexadesign/user/role/add.html.twig', [
            'form' => $form,
        ]);
    }

    public function copyAction(Request $request, Role $role): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('role', 'create'));

        $form = $this->createForm(RoleCopyType::class, new RoleCopyData($role));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (RoleCopyData $data): RedirectResponse {
                $roleCopyStruct = $this->roleCopyMapper->reverseMap($data);
                $role = $this->roleService->copyRole($data->getCopiedRole(), $roleCopyStruct);

                $this->notificationHandler->success(
                    /** @Desc("Role '%role%' copied.") */
                    'role.copy.success',
                    ['%role%' => $role->identifier],
                    'ibexa_role'
                );

                return new RedirectResponse($this->generateUrl('ibexa.role.view', [
                    'roleId' => $role->id,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->render('@ibexadesign/user/role/copy.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    public function updateAction(Request $request, Role $role): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('role', 'update'));
        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->formFactory->updateRole(
            new RoleUpdateData($role)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (RoleUpdateData $data): Response {
                /** @var \Ibexa\Contracts\Core\Repository\Values\User\Role $role */
                $role = $data->getRole();

                $roleUpdateStruct = $this->roleUpdateMapper->reverseMap($data);
                $roleDraft = $this->roleService->createRoleDraft($role);

                $this->roleService->updateRoleDraft($roleDraft, $roleUpdateStruct);
                $this->roleService->publishRoleDraft($roleDraft);

                $this->notificationHandler->success(
                    /** @Desc("Role '%role%' updated.") */
                    'role.update.success',
                    ['%role%' => $role->identifier],
                    'ibexa_role'
                );

                return new RedirectResponse($this->generateUrl('ibexa.role.view', [
                    'roleId' => $role->id,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->render('@ibexadesign/user/role/edit.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    public function deleteAction(Request $request, Role $role): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('role', 'delete'));
        $form = $this->formFactory->deleteRole(
            new RoleDeleteData($role)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (RoleDeleteData $data): RedirectResponse {
                $role = $data->getRole();
                $this->roleService->deleteRole($role);

                $this->notificationHandler->success(
                    /** @Desc("Role '%role%' removed.") */
                    'role.delete.success',
                    ['%role%' => $role->identifier],
                    'ibexa_role'
                );

                return new RedirectResponse($this->generateUrl('ibexa.role.list'));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->redirectToRoute('ibexa.role.view', [
            'roleId' => $role->id,
        ]);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function bulkDeleteAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('role', 'delete'));
        $form = $this->formFactory->deleteRoles(
            new RolesDeleteData()
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (RolesDeleteData $data): RedirectResponse {
                foreach ($data->getRoles() ?? [] as $roleId => $selected) {
                    $role = $this->roleService->loadRole($roleId);
                    $this->roleService->deleteRole($role);

                    $this->notificationHandler->success(
                        /** @Desc("Role '%role%' removed.") */
                        'role.delete.success',
                        ['%role%' => $role->identifier],
                        'ibexa_role'
                    );
                }

                return new RedirectResponse($this->generateUrl('ibexa.role.list'));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return new RedirectResponse($this->generateUrl('ibexa.role.list'));
    }
}
