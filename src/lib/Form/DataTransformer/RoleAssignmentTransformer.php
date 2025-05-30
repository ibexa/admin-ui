<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment as APIRoleAssignment;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforms between a Role Assignment's ID and a domain specific object.
 *
 * @phpstan-implements \Symfony\Component\Form\DataTransformerInterface<\Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment, int>
 */
class RoleAssignmentTransformer implements DataTransformerInterface
{
    protected RoleService $roleService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\RoleService $roleService
     */
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Transforms a domain specific RoleAssignment object into an ID.
     */
    public function transform(mixed $value): ?int
    {
        if (null === $value) {
            return null;
        }

        return $value->getId();
    }

    /**
     * Transforms a Role Assignment's ID into a domain specific RoleAssignment object.
     *
     * @param mixed $value
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\RoleAssignment|null
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function reverseTransform(mixed $value): ?APIRoleAssignment
    {
        if (empty($value)) {
            return null;
        }

        if (!ctype_digit($value)) {
            throw new TransformationFailedException('Expected a numeric string.');
        }

        try {
            return $this->roleService->loadRoleAssignment((int)$value);
        } catch (NotFoundException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
