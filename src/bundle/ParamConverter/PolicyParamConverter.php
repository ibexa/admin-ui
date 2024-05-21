<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\AdminUi\ParamConverter;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\Policy;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PolicyParamConverter implements ParamConverterInterface
{
    public const PARAMETER_ROLE_ID = 'roleId';
    public const PARAMETER_POLICY_ID = 'policyId';

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
        if (!$request->get(self::PARAMETER_ROLE_ID) || !$request->get(self::PARAMETER_POLICY_ID)) {
            return false;
        }

        $roleId = (int)$request->get(self::PARAMETER_ROLE_ID);

        try {
            $role = $this->roleService->loadRole($roleId);
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException("Role $roleId not found.");
        }

        $policyId = (int)$request->get(self::PARAMETER_POLICY_ID);

        foreach ($role->getPolicies() as $item) {
            if ($item->id === $policyId) {
                $request->attributes->set($configuration->getName(), $item);

                return true;
            }
        }

        throw new NotFoundHttpException("Policy $policyId not found.");
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return Policy::class === $configuration->getClass();
    }
}
