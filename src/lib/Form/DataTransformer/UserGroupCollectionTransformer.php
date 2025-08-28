<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\DataTransformer;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\UserService;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforms between comma separated string of UserGroups's ID and an array of domain specific UserGroup objects.
 */
final readonly class UserGroupCollectionTransformer implements DataTransformerInterface
{
    public function __construct(private UserService $userService)
    {
    }

    /**
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function transform(mixed $value): ?string
    {
        if (!is_array($value) || empty($value)) {
            return null;
        }

        return implode(',', array_column($value, 'id'));
    }

    /**
     * @return array<mixed>
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException if the given value is not an integer
     *                                                                         or if the value can not be transformed
     */
    public function reverseTransform(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        if (!is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        $idArray = explode(',', $value);

        try {
            return array_map([$this->userService, 'loadUserGroup'], $idArray);
        } catch (NotFoundException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
