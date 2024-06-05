<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\AdminUi\ParamConverter;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleAssignmentParamConverter implements ParamConverterInterface
{
    public const PRAMETER_ROLE_ASSIGNMENT_ID = 'assignmentId';

    /** @var \Ibexa\Contracts\Core\Repository\RoleService */
    private $roleService;

    /**
     * RoleParamConverter constructor.
     *
     * @param \Ibexa\Contracts\Core\Repository\RoleService $roleService
     */
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        if (!$request->get(self::PRAMETER_ROLE_ASSIGNMENT_ID)) {
            return false;
        }

        $roleAssigmentId = (int)$request->get(self::PRAMETER_ROLE_ASSIGNMENT_ID);

        try {
            $roleAssigment = $this->roleService->loadRoleAssignment($roleAssigmentId);
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException("Role assignment $roleAssigmentId not found.");
        }

        $request->attributes->set($configuration->getName(), $roleAssigment);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return RoleAssignment::class === $configuration->getClass();
    }
}
