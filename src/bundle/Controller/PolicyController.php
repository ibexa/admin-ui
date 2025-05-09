<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\Controller;

use Ibexa\AdminUi\Form\Data\Policy\PoliciesDeleteData;
use Ibexa\AdminUi\Form\Data\Policy\PolicyCreateData;
use Ibexa\AdminUi\Form\Data\Policy\PolicyDeleteData;
use Ibexa\AdminUi\Form\Data\Policy\PolicyUpdateData;
use Ibexa\AdminUi\Form\DataMapper\PolicyCreateMapper;
use Ibexa\AdminUi\Form\DataMapper\PolicyUpdateMapper;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\Form\Type\Policy\PolicyUpdateType;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\Policy;
use Ibexa\Contracts\Core\Repository\Values\User\Role;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Security\Authorization\Attribute;
use JMS\TranslationBundle\Annotation\Desc;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Button;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PolicyController extends Controller
{
    private TranslatableNotificationHandlerInterface $notificationHandler;

    private RoleService $roleService;

    private PolicyCreateMapper $policyCreateMapper;

    private PolicyUpdateMapper $policyUpdateMapper;

    private FormFactory $formFactory;

    private SubmitHandler $submitHandler;

    private ConfigResolverInterface $configResolver;

    public function __construct(
        TranslatableNotificationHandlerInterface $notificationHandler,
        RoleService $roleService,
        PolicyCreateMapper $policyCreateMapper,
        PolicyUpdateMapper $policyUpdateMapper,
        FormFactory $formFactory,
        SubmitHandler $submitHandler,
        ConfigResolverInterface $configResolver
    ) {
        $this->notificationHandler = $notificationHandler;
        $this->roleService = $roleService;
        $this->policyCreateMapper = $policyCreateMapper;
        $this->policyUpdateMapper = $policyUpdateMapper;
        $this->formFactory = $formFactory;
        $this->submitHandler = $submitHandler;
        $this->configResolver = $configResolver;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Role $role
     * @param string $routeName
     * @param int $policyPage
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Pagerfanta\Exception\OutOfRangeCurrentPageException
     * @throws \Pagerfanta\Exception\LessThan1CurrentPageException
     * @throws \Pagerfanta\Exception\LessThan1MaxPerPageException
     */
    public function listAction(Role $role, string $routeName, int $policyPage = 1): Response
    {
        $pagerfanta = new Pagerfanta(
            new ArrayAdapter(iterator_to_array($role->getPolicies()))
        );

        $pagerfanta->setMaxPerPage($this->configResolver->getParameter('pagination.policy_limit'));
        $pagerfanta->setCurrentPage(min($policyPage, $pagerfanta->getNbPages()));

        /** @var \Ibexa\Contracts\Core\Repository\Values\User\Policy[] $policies */
        $policies = $pagerfanta->getCurrentPageResults();

        $isEditable = [];
        foreach ($policies as $policy) {
            $limitationTypes = $policy->module
                ? $this->roleService->getLimitationTypesByModuleFunction($policy->module, $policy->function)
                : [];

            $isEditable[$policy->id] = !empty($limitationTypes);
        }

        $deletePoliciesForm = $this->formFactory->deletePolicies(
            new PoliciesDeleteData($role, $this->getPoliciesNumbers($policies))
        );

        return $this->render('@ibexadesign/user/policy/list.html.twig', [
            'form_policies_delete' => $deletePoliciesForm,
            'is_editable' => $isEditable,
            'role' => $role,
            'pager' => $pagerfanta,
            'route_name' => $routeName,
            'can_update' => $this->isGranted(new Attribute('role', 'update')),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Role $role
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     */
    public function createAction(Request $request, Role $role): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('role', 'update'));
        $form = $this->formFactory->createPolicy(
            new PolicyCreateData()
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (PolicyCreateData $data) use ($role): RedirectResponse {
                $policyCreateStruct = $this->policyCreateMapper->reverseMap($data);

                $limitationTypes = $policyCreateStruct->module
                    ? $this->roleService->getLimitationTypesByModuleFunction($policyCreateStruct->module, $policyCreateStruct->function)
                    : [];

                $isEditable = !empty($limitationTypes);

                if ($isEditable) {
                    $this->notificationHandler->success(
                        /** @Desc("Now you can set Limitations for the Policy.") */
                        'policy.add.set_limitation',
                        ['%role%' => $role->identifier],
                        'ibexa_role'
                    );

                    return new RedirectResponse($this->generateUrl('ibexa.policy.create_with_limitation', [
                        'roleId' => $role->id,
                        'policyModule' => $policyCreateStruct->module,
                        'policyFunction' => $policyCreateStruct->function,
                    ]));
                }

                try {
                    $this->roleService->deleteRoleDraft($this->roleService->loadRoleDraftByRoleId($role->id));
                } catch (NotFoundException $e) {
                }

                $roleDraft = $this->roleService->createRoleDraft($role);
                $roleDraft = $this->roleService->addPolicyByRoleDraft($roleDraft, $policyCreateStruct);
                $this->roleService->publishRoleDraft($roleDraft);

                $this->notificationHandler->success(
                    /** @Desc("Created new Policies in Role '%role%'.") */
                    'policy.add.success',
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

        return $this->render('@ibexadesign/user/policy/add.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Role $role
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Policy $policy
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \InvalidArgumentException
     */
    public function updateAction(Request $request, Role $role, Policy $policy): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('role', 'update'));
        $limitationTypes = $policy->module
            ? $this->roleService->getLimitationTypesByModuleFunction($policy->module, $policy->function)
            : [];

        $isEditable = !empty($limitationTypes);

        if (!$isEditable) {
            $this->notificationHandler->error(
                /** @Desc("Policy type '%policy%' does not contain Limitations.") */
                'policy.edit.no_limitations',
                ['%policy%' => $policy->module . '/' . $policy->function],
                'ibexa_role'
            );

            return new RedirectResponse($this->generateUrl('ibexa.role.view', [
                'roleId' => $role->id,
            ]));
        }

        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->formFactory->updatePolicy(
            new PolicyUpdateData($policy)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (PolicyUpdateData $data) use ($role, $policy, $form): Response {
                $policyUpdateStruct = $this->policyUpdateMapper->reverseMap($data);

                $roleDraft = $this->roleService->createRoleDraft($role);
                $policies = $roleDraft->getPolicies();
                foreach ($policies as $policyDraft) {
                    if ($policyDraft->originalId === $policy->id) {
                        $this->roleService->updatePolicyByRoleDraft($roleDraft, $policyDraft, $policyUpdateStruct);
                        $this->roleService->publishRoleDraft($roleDraft);
                        break;
                    }
                }

                $this->notificationHandler->success(
                    /** @Desc("Updated Policies in Role '%role%'.") */
                    'policy.update.success',
                    ['%role%' => $role->identifier],
                    'ibexa_role'
                );

                if ($form->getClickedButton() instanceof Button
                    && $form->getClickedButton()->getName() === PolicyUpdateType::BTN_SAVE
                ) {
                    return $this->redirectToRoute('ibexa.policy.update', [
                        'roleId' => $roleDraft->id,
                        'policyId' => $policy->id,
                    ]);
                }

                return new RedirectResponse($this->generateUrl('ibexa.role.view', [
                    'roleId' => $role->id,
                ]));
            });

            if ($result instanceof Response) {
                return $result;
            }
        }

        return $this->render('@ibexadesign/user/policy/edit.html.twig', [
            'role' => $role,
            'policy' => $policy,
            'form' => $form,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Role $role
     * @param string $policyModule
     * @param string $policyFunction
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createWithLimitationAction(Request $request, Role $role, string $policyModule, string $policyFunction): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('role', 'update'));
        $form = $this->formFactory->createPolicyWithLimitation(
            (new PolicyCreateData())->setPolicy([
                'module' => $policyModule,
                'function' => $policyFunction,
            ])
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (PolicyCreateData $data) use ($role): RedirectResponse {
                $policyCreateStruct = $this->policyCreateMapper->reverseMap($data);
                $roleDraft = $this->roleService->createRoleDraft($role);
                $roleDraft = $this->roleService->addPolicyByRoleDraft($roleDraft, $policyCreateStruct);
                $this->roleService->publishRoleDraft($roleDraft);

                $this->notificationHandler->success(
                    /** @Desc("Created new Policies in Role '%role%'.") */
                    'policy.add.success',
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

        return $this->render('@ibexadesign/user/policy/create_with_limitation.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Role $role
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Policy $policy
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     */
    public function deleteAction(Request $request, Role $role, Policy $policy): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('role', 'update'));
        $form = $this->formFactory->deletePolicy(
            new PolicyDeleteData($policy)
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (PolicyDeleteData $data) use ($role): RedirectResponse {
                $roleDraft = $this->roleService->createRoleDraft($role);
                foreach ($roleDraft->getPolicies() as $policyDraft) {
                    if ($policyDraft->originalId == $data->getId()) {
                        $this->roleService->removePolicyByRoleDraft($roleDraft, $policyDraft);
                        $this->roleService->publishRoleDraft($roleDraft);
                        break;
                    }
                }

                $this->notificationHandler->success(
                    /** @Desc("Removed Policies from Role '%role%'.") */
                    'policy.delete.success',
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

        return $this->redirectToRoute('ibexa.role.view', [
            'roleId' => $role->id,
        ]);
    }

    /**
     * Handles removing policies based on submitted form.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Role $role
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \InvalidArgumentException
     */
    public function bulkDeleteAction(Request $request, Role $role): Response
    {
        $this->denyAccessUnlessGranted(new Attribute('role', 'update'));
        $form = $this->formFactory->deletePolicies(
            new PoliciesDeleteData()
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $result = $this->submitHandler->handle($form, function (PoliciesDeleteData $data) use ($role): RedirectResponse {
                $roleDraft = $this->roleService->createRoleDraft($role);

                foreach ($data->getPolicies() as $policyId => $selected) {
                    foreach ($roleDraft->getPolicies() as $policyDraft) {
                        if ($policyDraft->originalId === $policyId) {
                            $this->roleService->removePolicyByRoleDraft($roleDraft, $policyDraft);
                        }
                    }
                }

                $this->roleService->publishRoleDraft($roleDraft);

                $this->notificationHandler->success(
                    /** @Desc("Removed Policies from Role '%role%'.") */
                    'policy.delete.success',
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

        return $this->redirectToRoute('ibexa.role.view', [
            'roleId' => $role->id,
        ]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Policy[] $policies
     *
     * @return array
     */
    private function getPoliciesNumbers(array $policies): array
    {
        $policiesNumbers = array_column($policies, 'id');

        return array_combine($policiesNumbers, array_fill_keys($policiesNumbers, false));
    }
}
