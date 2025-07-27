<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\Role as APIRole;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforms between a Role's ID and a domain specific object.
 *
 * @phpstan-implements \Symfony\Component\Form\DataTransformerInterface<\Ibexa\Contracts\Core\Repository\Values\User\Role, int>
 */
class RoleTransformer implements DataTransformerInterface
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Transforms a domain specific Role object into a Role id.
     */
    public function transform(mixed $value): ?int
    {
        if (null === $value) {
            return null;
        }

        return $value->id;
    }

    /**
     * Transforms a Role ID into a domain specific Role object.
     */
    public function reverseTransform(mixed $value): ?APIRole
    {
        if (empty($value)) {
            return null;
        }

        if (!ctype_digit($value)) {
            throw new TransformationFailedException('Expected a numeric string.');
        }

        try {
            return $this->roleService->loadRole((int)$value);
        } catch (NotFoundException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
